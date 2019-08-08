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
use Symfony\Component\Stopwatch\Stopwatch;
use App\Utils\ConstantsUtil;

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

        $stopwatch = new Stopwatch(true);
        $stopwatch->start('ldapImport');
        $result = $this
            ->ldapImport
            ->import($argumentValue)
        ;
        $event = $stopwatch->stop('ldapImport');

        $symfonyStyle->note(sprintf('Process time: %dms', $event->getDuration()));
        $symfonyStyle->table(array_keys($result), [$result]);
    }
}
