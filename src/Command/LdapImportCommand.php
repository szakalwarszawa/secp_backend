<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Ldap\Import\LdapImport;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use App\Ldap\Constants\ImportResources;
use InvalidArgumentException;
use App\Utils\ConstantsUtil;
use App\Ldap\Constants\ArrayResponseFormats;
use Symfony\Component\Console\Helper\Table;
use App\Ldap\Import\Updater\Result\Types;

/**
 * Class LdapImportCommand
 */
class LdapImportCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:ldap-import';

    /**
     * @var LdapImport
     */
    private $ldapImport;

    /**
     * @var ConstantsUtil
     */
    private $constantsUtil;

    /**
     * @param LdapImport $ldapImport
     */
    public function __construct(LdapImport $ldapImport)
    {
        $this->ldapImport = $ldapImport;
        $this->constantsUtil = new ConstantsUtil(ImportResources::class);

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Import users, departments, sections from AD')
            ->addArgument(
                'resource',
                InputArgument::OPTIONAL,
                $this->constantsUtil::stringify(),
                $this->constantsUtil::valueToKey(ImportResources::IMPORT_ALL)
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $argumentValue = $this->constantsUtil::keyToValue($input->getArgument('resource'));
        if (null === $argumentValue) {
            throw new InvalidArgumentException('Incorrect resource to import.');
        }
        $symfonyStyle = new SymfonyStyle($input, $output);

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Do you want to continue? [Y/n]');
        if (!$helper->ask($input, $output, $question)) {
            $symfonyStyle->error('Ldap import aborted.');

            return;
        }

        $result = $this
            ->ldapImport
            ->setResponseFormat(ArrayResponseFormats::COUNTER_SUCCESS_FAILED)
            ->import($argumentValue)
        ;

        $stopwatchEvent = $this->ldapImport->getStopWatchResult();
        $symfonyStyle->note(
            sprintf(
                'Process time: %dms, Memory: %d',
                $stopwatchEvent->getDuration(),
                $stopwatchEvent->getMemory()
            )
        );

        $this->printResultTable($output, $result);
    }

    /**
     * Prints result table.
     *
     * @param OutputInterface $output
     * @param array $result
     *
     * @return void
     */
    private function printResultTable(OutputInterface $output, array $result): void
    {
        $table = new Table($output);
        $table
            ->setHeaders(['key', Types::SUCCESS, Types::FAIL]);

        foreach ($result as $key => $value) {
            $table
                ->addRow([$key, $value[Types::SUCCESS], $value[Types::FAIL]])
            ;
        }

        $table->render();
    }
}
