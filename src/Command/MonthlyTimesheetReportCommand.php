<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Department;
use App\Entity\Section;
use App\Exception\GeneratorNotReadyException;
use App\Utils\MonthlyReportGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;

/**
 * Class MonthlyTimesheetReportCommand
 */
class MonthlyTimesheetReportCommand extends Command
{
    /**
     * @var SymfonyStyle|null
     */
    private $symfonyStyle;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var MonthlyReportGenerator
     */
    private $monthlyReportGenerator;

    /**
     * @param EntityManagerInterface $entityManager
     * @param MonthlyReportGenerator $monthlyReportGenerator
     */
    public function __construct(EntityManagerInterface $entityManager, MonthlyReportGenerator $monthlyReportGenerator)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->monthlyReportGenerator = $monthlyReportGenerator;
    }

    /**
     * @var string
     */
    protected static $defaultName = 'app:monthly-timesheet-report';

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Generate monthly timesheetDays report and saves it to file.')
            ->addArgument('month', InputArgument::REQUIRED, 'Month to report')
            ->addOption('section', null, InputOption::VALUE_REQUIRED, 'Report for section [Priority 3]')
            ->addOption('department', null, InputOption::VALUE_REQUIRED, 'Report for department [Priority 2]')
            ->addOption('all', null, InputOption::VALUE_NONE, 'All users report [Priority 1]')
        ;
    }

    /**
     * {@inheritDoc}
     *
     * @throws EntityNotFoundException
     * @throws GeneratorNotReadyException
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->symfonyStyle = new SymfonyStyle($input, $output);
        $this->monthlyReportGenerator->setMonthRange((int) $input->getArgument('month'));

        if ($input->getOption('all')) {
            $result = $this->monthlyReportGenerator->generateAllReport();
            $this->printResult($result);

            return;
        }

        if ($input->getOption('department')) {
            $this->generateReportForDepartment($input->getOption('department'));

            return;
        }

        if ($input->getOption('section')) {
            $this->generateReportForSection($input->getOption('section'));

            return;
        }

        $this->symfonyStyle->caution('Source not provided.');
    }

    /**
     * @param string $departmentName
     *
     * @return void
     * @throws EntityNotFoundException
     * @throws GeneratorNotReadyException
     */
    private function generateReportForDepartment(string $departmentName): void
    {
        $department = $this
            ->entityManager
            ->getRepository(Department::class)
            ->findOneBy([
                'name' => $departmentName
            ]);
        if (!$department) {
            throw new EntityNotFoundException(
                sprintf('Department %s not found.', $departmentName)
            );
        }

        $result = $this->monthlyReportGenerator->generateDepartmentReport($department);
        $this->printResult($result);
    }

    /**
     * @param string $sectionName
     *
     * @return void
     * @throws EntityNotFoundException
     * @throws GeneratorNotReadyException
     */
    private function generateReportForSection(string $sectionName): void
    {
        $section = $this
            ->entityManager
            ->getRepository(Section::class)
            ->findOneBy([
                'name' => $sectionName,
            ]);
        if (!$section) {
            throw new EntityNotFoundException(
                sprintf('Section %s not found.', $sectionName)
            );
        }
        $result = $this->monthlyReportGenerator->generateSectionReport($section);
        $this->printResult($result);
    }

    /**
     * Prints result in console.
     *
     * @param string|null $result
     *
     * @return void
     */
    private function printResult(?string $result): void
    {
        if (!$result) {
            $this->symfonyStyle->warning('Report was not created. Probably there is no data in given month.');

            return;
        }

        $this->symfonyStyle->success(sprintf(
            'Report was created in directory %s',
            $result
        ));
    }
}
