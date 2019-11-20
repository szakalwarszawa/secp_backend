<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\MonthlyTimesheetReportCommand;
use App\DataFixtures\DepartmentFixtures;
use App\Tests\AbstractWebTestCase;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class MonthlyTimesheetReportCommandTest
 */
class MonthlyTimesheetReportCommandTest extends AbstractWebTestCase
{
    /**
     * @var CommandTester
     */
    private $commandTester;

    /**
     * Test case #1
     * Execute without required argument.
     *
     * @return void
     */
    public function testCase1(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "month")');
        $this->commandTester->execute([]);
    }

    /**
     * Test case #2
     * Execute without required source (department, all, section option)
     *
     * @return void
     */
    public function testCase2(): void
    {
        $this->commandTester->execute(['month' => random_int(1, 12)]);
        $this->assertStringContainsString('Source not provided.', $this->commandTester->getDisplay());
    }

    /**
     * Test case #3
     * Execute with non-existing department.
     *
     * @return void
     */
    public function testCase3(): void
    {
        $randomName = bin2hex(random_bytes(5));
        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Department %s not found',
                $randomName
            )
        );
        $this->commandTester->execute(
            [
                'month' => random_int(1, 12),
                '--department' => $randomName,
            ]
        );
    }

    /**
     * Test case #4
     * Execute with non-existing section.
     *
     * @return void
     */
    public function testCase4(): void
    {
        $randomName = bin2hex(random_bytes(5));
        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Section %s not found',
                $randomName
            )
        );
        $this->commandTester->execute(
            [
                'month' => random_int(1, 12),
                '--section' => $randomName,
            ]
        );
    }

    /**
     * Test case #5
     * Execute with existing department, but there is no sample data.
     *
     * @return void
     */
    public function testCase5(): void
    {
        $departmentAdmin = $this->getEntityFromReference(DepartmentFixtures::REF_DEPARTMENT_ADMIN);
        $this->commandTester->execute(
            [
                'month' => random_int(6, 12),
                '--department' => $departmentAdmin->getName(),
            ]
        );

        $this->assertStringContainsString(
            'Report was not created. Probably there is no data in given month.',
            $this->commandTester->getDisplay()
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $application = new Application(self::getKernelClass());
        $application->add(self::$container->get(MonthlyTimesheetReportCommand::class));
        $command = $application->find(MonthlyTimesheetReportCommand::getDefaultName());
        $this->commandTester = new CommandTester($command);
    }
}
