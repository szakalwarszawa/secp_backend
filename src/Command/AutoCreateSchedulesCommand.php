<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Department;
use App\Entity\Section;
use App\Entity\User;
use App\Utils\WorkScheduleCreator;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Class AutoCreateSchedulesCommand
 */
class AutoCreateSchedulesCommand extends Command
{
    /**
     * @var string
     */
    private const SINGLE_USER = 'username';

    /**
     * @var string
     */
    private const ALL_USERS = 'all';

    /**
     * @var string
     */
    private const ONLY_SECTION = 'section';

    /**
     * @var string
     */
    private const ONLY_DEPARTMENT = 'department';

    /**
     * @var string
     */
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
            ->setDescription(
                <<<'TXT'
Creates work schedules (for next reference period or specified date range) users or single user (`username` target).
TXT
            )
            ->addOption(
                'target',
                null,
                InputOption::VALUE_REQUIRED,
                sprintf(
                    'Create schedules for user/users by (this is an User::class property name) %s',
                    implode(', ', $this->availableOptions())
                ),
                self::ALL_USERS
            )
            ->addOption(
                'value',
                'val',
                InputOption::VALUE_OPTIONAL,
                'Target value to search users. (omit when target is `all`)'
            )
            ->addOption('fromDate', 'f', InputOption::VALUE_OPTIONAL, 'Schedule will be created from date.')
            ->addOption('toDate', 't', InputOption::VALUE_OPTIONAL, 'Schedule will be created to date.')
        ;
    }

    /**
     * @return array
     */
    private function availableOptions(): array
    {
        return [
            self::ONLY_DEPARTMENT,
            self::ONLY_SECTION,
            self::SINGLE_USER,
            self::ALL_USERS,
        ];
    }

    /**
     * {@inheritDoc}
     *
     * @throws EntityNotFoundException
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->resolveOptions($input);
        $this->symfonyStyle = new SymfonyStyle($input, $output);

        $stopwatch = new Stopwatch();
        $stopwatch->start('measure');
        $options = $input->getOptions();
        $this->beginCreationProcess($options['target'], $options['value']);

        $stopwatch->stop('measure');

        $this->symfonyStyle->newLine(2);
        $this->symfonyStyle->success(
            sprintf(
                'Time: %ss',
                $stopwatch->getEvent('measure')->getDuration() / 1000
            )
        );
    }

    /**
     * @param string $target
     * @param null|string $value
     *
     * @return void
     * @throws EntityNotFoundException
     */
    private function beginCreationProcess(string $target, ?string $value = null): void
    {
        switch ($target) {
            case self::ALL_USERS:
                $this->createSchedulesForUserList($this->getUsersList($target));
                break;
            case self::SINGLE_USER:
                $this->createSchedulesForUserList($this->getUsersList($target, $value));
                break;
            case self::ONLY_DEPARTMENT:
                $this->createSchedulesForOrganizationalUnit(self::ONLY_DEPARTMENT, $value);
                break;
            case self::ONLY_SECTION:
                $this->createSchedulesForOrganizationalUnit(self::ONLY_SECTION, $value);
                break;
        }
    }

    /**
     * Finds department/section by name and pass it to creation.
     *
     * @param string $target
     * @param string $value
     *
     * @return void
     * @throws EntityNotFoundException
     */
    private function createSchedulesForOrganizationalUnit(string $target, string $value): void
    {
        /**
         * @var Section|Department|null $ouEntity
         */
        $ouEntity = $this
            ->entityManager
            ->getRepository(
                $target === self::ONLY_SECTION ? Section::class : Department::class
            )
            ->findOneBy([
                'name' => $value,
            ])
        ;

        if ($ouEntity) {
            $question = new ConfirmationQuestion(
                sprintf(
                    'Proceed to create schedules for `%s` %s?',
                    $ouEntity->getName(),
                    $target
                )
            );
            $createSchedules = $this->symfonyStyle->askQuestion($question);

            if (!$createSchedules) {
                $this->symfonyStyle->caution('Aborted');

                return;
            }

            $this->createSchedulesForUserList($this->getUsersList($target, $ouEntity->getId()));

            return;
        }

        throw new EntityNotFoundException(
            sprintf(
                'Unable to find entity for %s - %s',
                $target,
                $value
            )
        );
    }

    /**
     * Get user/users to create schedules.
     *
     * @param string $target
     * @param mixed $value
     *
     * @return User[]
     */
    private function getUsersList(string $target, $value = null): array
    {
        $repository = $this
            ->entityManager
            ->getRepository(User::class)
            ;

        if ($target === self::ALL_USERS) {
            return $repository->findAll();
        }

        return $repository
            ->findBy([
                $target => $value,
            ]);
    }

    /**
     * Loop to create schedules for single user.
     *
     * @param User[] $userList
     *
     * @return void
     */
    private function createSchedulesForUserList(array $userList): void
    {
        $this->symfonyStyle->progressStart(count($userList));
        foreach ($userList as $user) {
            $this->symfonyStyle->progressAdvance();
            $this->createScheduleForUser($user);
        }
    }

    /**
     * Pass data to WorkScheduleCreator.
     *
     * @param User $user
     *
     * @return void
     */
    private function createScheduleForUser(User $user): void
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
     * @throws InvalidOptionException when target is different than 'all' and no value is provided
     * @throws InvalidOptionException when target is invalid
     * @throws Exception when invalid date string was passed to DateTime constructor
     */
    private function resolveOptions(InputInterface $input): void
    {
        $options = $input->getOptions();
        if (isset($options['fromDate']) !== isset($options['toDate'])) {
            throw new InvalidOptionException('If you provide date range, it must be two values - fromDate and toDate');
        }

        if (!in_array($options['target'], $this->availableOptions())) {
            throw new InvalidOptionException(
                sprintf(
                    'Invalid target. Available: %s',
                    implode(', ', $this->availableOptions())
                )
            );
        }

        if ($options['target'] !== self::ALL_USERS && !$options['value']) {
            throw new InvalidOptionException(
                'You need to provide `value` option.'
            );
        }

        if ($options['fromDate']) {
            $this->dateRange = [
                new DateTime($options['fromDate']),
                new DateTime($options['toDate'])
            ];
        }
    }
}
