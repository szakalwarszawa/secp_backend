<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\DataFixtures\UserFixtures;
use App\Entity\PresenceType;
use App\Entity\User;
use App\Entity\UserTimesheetDay;
use App\Entity\UserWorkScheduleDay;
use App\Tests\AbstractWebTestCase;
use App\Utils\MonthlyReportGenerator;
use DateTime;
use ZipArchive;

/**
 * Class MonthlyReportGeneratorTest
 */
class MonthlyReportGeneratorTest extends AbstractWebTestCase
{
    /**
     * @var MonthlyReportGenerator
     */
    private $monthlyReportGenerator;

    /**
     * @var int
     */
    private $insertedTimesheetDaysCounter = 0;

    /**
     * @var int
     */
    private $monthNumber;

    /**
     * @var User
     */
    private $user;

    /**
     * test getUserCsvContent
     *
     * @return void
     */
    public function testGetUserTimesheetDaysAsCsv(): void
    {
        $this->insertTimesheetDaysSample($this->user, $this->monthNumber);
        $monthlyReportGenerator = $this->monthlyReportGenerator;
        $csvData = $monthlyReportGenerator->setMonthRange($this->monthNumber)->getUserTimesheetDaysAsCsv($this->user);
        $this->assertContentCsv($csvData);
    }

    /**
     * test generateAllReport()
     *
     * @return void
     */
    public function testGenerateAllReport(): void
    {
        $monthlyReportGenerator = $this->monthlyReportGenerator;
        $result = $monthlyReportGenerator->setMonthRange($this->monthNumber)->generateAllReport();
        $this->assertNotNull($result);

        $fileExists = file_exists($result);
        $this->assertTrue($fileExists);

        $zipArchive = new ZipArchive();
        $zipArchive->open($result);
        $zipFilesCount = $zipArchive->numFiles;
        $this->assertEquals(1, $zipFilesCount);
        $this->assertContentCsv($zipArchive->getFromIndex(0));
    }

    /**
     * Check if given string is formatted as csv file.
     *
     * @param string $csvData
     *
     * @return void
     */
    private function assertContentCsv(string $csvData): void
    {
        $csvDataArray = array_filter(explode("\n", $csvData));
        $this->assertGreaterThan(1, count($csvDataArray));
        /**
         * Csv contains day start and end time.
         */
        $this->assertStringContainsString(',8:00,16:00', $csvDataArray[0]);

        /**
         * Csv contains working time
         */
        $this->assertStringContainsString(',8,', $csvDataArray[0]);

        /**
         * Csv contains used month.
         */
        $this->assertStringContainsString(
            sprintf(
                '-%s-',
                $this->monthNumber
            ),
            $csvDataArray[0]
        );
    }

    /**
     * Creates UserTimesheetDays sample.
     *
     * @param User $user
     * @param int $month
     *
     * @return void
     */
    private function insertTimesheetDaysSample(User $user, int $month): void
    {
        $startDate = date(sprintf(
            'Y-%s-1',
            $month
        ));
        $endDate = date(sprintf(
            'Y-%s-28',
            $month
        ));

        $userWorkScheduleDays = $this
            ->entityManager
            ->getRepository(UserWorkScheduleDay::class)
            ->findWorkDayBetweenDate($user, $startDate, $endDate)
        ;
        $this->assertNotEmpty($userWorkScheduleDays);

        $presenceType = $this->entityManager->getRepository(PresenceType::class)->findOneBy(['shortName' => 'O']);

        /**
         * Create timesheetDays for working days.
         */
        foreach ($userWorkScheduleDays as $userWorkScheduleDay) {
            if ($userWorkScheduleDay->getDayDefinition()->getWorkingDay()) {
                $timesheetDay = new UserTimesheetDay();
                $timesheetDay
                    ->setDayStartTime('8:00')
                    ->setDayEndTime('16:00')
                    ->setPresenceType($presenceType)
                    ->setWorkingTime(8)
                    ->setUserWorkScheduleDay($userWorkScheduleDay)
                ;
                $this->entityManager->persist($timesheetDay);
                $this->insertedTimesheetDaysCounter++;
            }
        }

        $this->entityManager->flush();

        $timesheetDays = $this
            ->entityManager
            ->getRepository(UserTimesheetDay::class)
            ->findTimesheetDaysBetweenDate($user, $startDate, $endDate)
        ;

        $this->assertNotEmpty($timesheetDays);
        $this->assertCount($this->insertedTimesheetDaysCounter, $timesheetDays);
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->monthlyReportGenerator = self::$container->get(MonthlyReportGenerator::class);
        $monthNumber = (int) (new DateTime())->format('m');
        $this->monthNumber = $monthNumber;
        $user = $this->getEntityFromReference(UserFixtures::REF_USER_USER);
        $this->user = $user;
    }
}
