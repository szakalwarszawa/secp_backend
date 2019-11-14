<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\AutoCreateSchedulesCommand;
use App\DataFixtures\DepartmentFixtures;
use App\DataFixtures\SectionFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Section;
use App\Tests\AbstractWebTestCase;
use App\Utils\WorkScheduleCreator;
use Exception;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class AutoCreateSchedulesCommandTest
 */
class AutoCreateSchedulesCommandTest extends AbstractWebTestCase
{
    /**
     * @var CommandTester
     */
    private $commandTester;

    /**
     * Test case #1
     * Pass fromDate option without toDate option.
     *
     * @return void
     */
    public function testCase1(): void
    {
        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage('If you provide date range, it must be two values - fromDate and toDate');
        $this->commandTester->execute(
            [
                '--fromDate' => '2019-01-01',
            ]
        );
    }

    /**
     * Test case #2
     * Pass toDate option without fromDate option.
     */
    public function testCase2(): void
    {
        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage('If you provide date range, it must be two values - fromDate and toDate');
        $this->commandTester->execute(
            [
                '--toDate' => '2019-01-01',
            ]
        );
    }

    /**
     * Test case #3
     * Pass different target option than 'all' without passing a value.
     */
    public function testCase3(): void
    {
        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage('You need to provide `value` option.');
        $this->commandTester->execute(
            [
                '--target' => 'department',
            ]
        );
    }

    /**
     * Test case #4
     * Pass invalid target option.
     *
     * @throws Exception
     */
    public function testCase4(): void
    {
        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage('Invalid target. Available: department, section, username, all');
        $this->commandTester->execute(
            [
                '--target' => bin2hex(random_bytes(5)),
            ]
        );
    }

    /**
     * Test case #5
     * Attempt to create schedule to non-existing user.
     *
     * @throws Exception
     */
    public function testCase5(): void
    {
        $this->commandTester->execute(
            [
                '--target' => 'username',
                '--value' => bin2hex(random_bytes(5)),
            ]
        );

        $commandDisplay = $this->commandTester->getDisplay();
        /**
         * Created 0 schedules.
         */
        $this->assertStringContainsString('0 [', $commandDisplay);
    }

    /**
     * Test case #6
     * Create schedule for single user.
     */
    public function testCase6(): void
    {
        $this->commandTester->execute(
            [
                '--target' => 'username',
                '--value' => $this->getEntityFromReference(UserFixtures::REF_USER_USER)->getUsername(),
            ]
        );

        $commandDisplay = $this->commandTester->getDisplay();
        /**
         * Contains success message.
         */
        $this->assertStringContainsString('[OK]', $commandDisplay);

        /**
         * Created only 1 schedule.
         */
        $this->assertStringContainsString('1/1', $commandDisplay);
    }

    /**
     * Test case 7
     * Attempt to create schedules for whole department.
     * But command need user confirm to proceed.
     * --no-interaction option was NOT passed.
     *
     * @return void
     */
    public function testCase7(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Aborted.');
        $this->commandTester->execute(
            [
                '--target' => 'department',
                '--value' => $this->getEntityFromReference(DepartmentFixtures::REF_DEPARTMENT_ADMIN)->getName(),
            ]
        );
    }

    /**
     * Test case 8
     * Create schedules for whole section.
     */
    public function testCase8(): void
    {
        /**
         * @var Section $departmentEntity
         */
        $sectionEntity = $this->getEntityFromReference(SectionFixtures::REF_HR_SECTION);

        /**
         * Answer YES to first question. (--no-interaction)
         */
        $this->commandTester->setInputs(['yes']);
        $this->commandTester->execute(
            [
                '--target' => 'section',
                '--value' => $sectionEntity->getName(),
            ]
        );

        $commandDisplay = $this->commandTester->getDisplay();

        /**
         * Contains success message.
         */
        $this->assertStringContainsString('[OK]', $commandDisplay);
        $this->assertStringContainsString(
            sprintf(
                'Proceed to create schedules for `%s` section?',
                $sectionEntity->getName()
            ),
            $commandDisplay
        );

        $usersInSection = $sectionEntity->getUsers()->count();

        /**
         * Command should create as many schedules as section users count.
         */
        $this->assertStringContainsString(
            sprintf('%s/%s', $usersInSection, $usersInSection),
            $commandDisplay
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $application = new Application(self::getKernelClass());
        $application->add(new AutoCreateSchedulesCommand(
            $this->entityManager,
            self::$container->get(WorkScheduleCreator::class)
        ));
        $command = $application->find(AutoCreateSchedulesCommand::getDefaultName());
        $this->commandTester = new CommandTester($command);
    }
}
