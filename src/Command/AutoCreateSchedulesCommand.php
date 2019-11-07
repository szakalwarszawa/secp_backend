<?php

namespace App\Command;

use App\Entity\User;
use App\Utils\WorkScheduleCreator;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\VarDumper\VarDumper;

class AutoCreateSchedulesCommand extends Command
{
    protected static $defaultName = 'app:auto-create-schedules';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var WorkScheduleCreator
     */
    private $workScheduleCreator;

    /**
     * @var array
     */
    private $dateRange = [];

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @param EntityManagerInterface $entityManager
     * @param WorkScheduleCreator $workScheduleCreator
     */
    public function __construct(EntityManagerInterface $entityManager, WorkScheduleCreator $workScheduleCreator)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->workScheduleCreator = $workScheduleCreator;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('username', InputArgument::OPTIONAL, 'Username to create schedule.')
            ->addOption('allUsers', 'all', InputOption::VALUE_NONE, 'Schedules will be created for all users.')
            ->addOption('fromDate', 'f', InputOption::VALUE_OPTIONAL, 'Schedule will be created from date.')
            ->addOption('toDate', 't', InputOption::VALUE_OPTIONAL, 'Schedule will be created to date.')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->resolveOptions($input);
        $this->symfonyStyle = new SymfonyStyle($input, $output);
        $stopwatch = new Stopwatch();
        $stopwatch->start('measure');
        if ($input->getOption('allUsers')) {
            $this->createSchedulesForAllUsers();
        }

        if ($input->getArgument('username')) {
            $user = $this
                ->entityManager
                ->getRepository(User::class)
                ->findOneBy([
                    'username' => $input->getArgument('username')
                ]);

            $this->createScheduleForUser($user);
        }
        $stopwatch->stop('measure');

        $this->symfonyStyle->success(
            sprintf(
                '%s',
                $stopwatch->getEvent('measure')->getDuration()
            )
        );
    }

    private function createSchedulesForAllUsers()
    {
        $allUsers = $this
            ->entityManager
            ->getRepository(User::class)
            ->findAll()
        ;

        $this->symfonyStyle->progressStart(count($allUsers));

        foreach ($allUsers as $user) {
            $this->symfonyStyle->progressAdvance();
            $this->createScheduleForUser($user);
        }
    }

    private function createScheduleForUser(User $user)
    {
        $this->workScheduleCreator->createWorkSchedule($user, $this->dateRange);
    }

    /**
     * Resolve options correctness.
     *
     * @param InputInterface $input
     *
     * @return void
     * @throws InvalidOptionException when only one value from range is provided
     * @throws InvalidOptionException when username and allUsers option are defined
     * @throws Exception when invalid date string was passed to DateTime constructor
     */
    private function resolveOptions(InputInterface $input): void
    {
        $options = $input->getOptions();
        if (isset($options['fromDate']) <> isset($options['toDate'])) {
            throw new InvalidOptionException('If you provide date range, it must be two values - fromDate and toDate');
        }

        if ($options['allUsers'] && $input->getArgument('username')) {
            throw new InvalidOptionException(
                'You can not create schedule for single user and all users at the same time'
            );
        }

        if ($options['fromDate']) {
            $this->dateRange = [
                new DateTime($options['fromDate']),
                new DateTime($options['toDate'])
            ];
        }

        return;
    }
}
