<?php

declare(strict_types=1);

namespace App\Utils;

use App\Entity\Department;
use App\Entity\Section;
use App\Entity\User;
use App\Entity\UserTimesheetDay;
use App\Exception\GeneratorNotReadyException;
use App\Repository\UserTimesheetDayRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use ErrorException;
use Exception;
use InvalidArgumentException;
use League\Csv\Writer;
use ZipArchive;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Class MonthlyReportGenerator
 */
class MonthlyReportGenerator
{
    /**
     * @var string
     */
    private const DATE_PLACEHOLDER = '{date}';

    /**
     * @var string
     */
    private const MONTH_PLACEHOLDER = '{month}';

    /**
     * @var string
     */
    private const FILE_EXTENSION = '.zip';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var array
     */
    private $dateRange = [];

    /**
     * @var ZipArchive
     */
    private $zipArchive;

    /**
     * @var string
     */
    private $reportSavePath;

    /**
     * @var string
     */
    private $reportFilename;

    /**
     * @var string
     */
    private $fullFilePath;

    /**
     * @param string $reportSavePath
     * @param string $reportFilename
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(string $reportSavePath, string $reportFilename, EntityManagerInterface $entityManager)
    {
        $this->reportSavePath = $reportSavePath;
        $this->reportFilename = $reportFilename;
        $this->entityManager = $entityManager;
    }

    /**
     * Build full path to report file.
     *
     * @todo Probably path should be built with __DIR__ const.
     *
     * @return void
     */
    private function buildSavePath(): void
    {
        if (substr($this->reportSavePath, -1) !== '/') {
            $this->reportSavePath .= '/';
        }

        if (strpos($this->reportFilename, self::DATE_PLACEHOLDER)) {
            $currentDate = (new DateTime())->format('Y-m-d_H:i:s');
            $this->reportFilename = str_replace(self::DATE_PLACEHOLDER, $currentDate, $this->reportFilename);
        }

        if (strpos($this->reportFilename, self::MONTH_PLACEHOLDER)) {
            $this->reportFilename = str_replace(
                self::MONTH_PLACEHOLDER,
                current($this->dateRange)->format('F'),
                $this->reportFilename
            );
        }

        $this->fullFilePath = $this->reportSavePath . $this->reportFilename . self::FILE_EXTENSION;
    }

    /**
     * @param int $month
     *
     * @return MonthlyReportGenerator
     * @throws GeneratorNotReadyException when month number is not in range 1-12.
     * @throws Exception
     */
    public function setMonthRange(int $month): MonthlyReportGenerator
    {
        if ($month > 12 || $month < 1) {
            throw new GeneratorNotReadyException('Invalid month');
        }

        $monthFirstDay = date(sprintf('Y-%s-1', $month));
        $dateTimeFirstDay = new DateTimeImmutable($monthFirstDay);

        $this->dateRange = [
            $dateTimeFirstDay,
            $dateTimeFirstDay->modify('Last day of this month'),
        ];

        $this->buildSavePath();

        return $this;
    }

    /**
     * Initialize zip file.
     *
     * @return void
     */
    private function initializeZipArchive(): void
    {
        $zipArchive = new ZipArchive();
        $zipArchive->open($this->fullFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        VarDumper::dump($this->fullFilePath);
        $this->zipArchive = $zipArchive;
    }

    /**
     * Generate report for department users.
     *
     * @param Department $department
     *
     * @return null|string
     * @throws GeneratorNotReadyException
     */
    public function generateDepartmentReport(Department $department): ?string
    {
        return $this->createReportForUserList($department->getUsers());
    }

    /**
     * Generate report for section users.
     *
     * @param Section $section
     *
     * @return null|string
     * @throws GeneratorNotReadyException
     */
    public function generateSectionReport(Section $section): ?string
    {
        return $this->createReportForUserList($section->getUsers());
    }

    /**
     * Generate report for all users.
     *
     * @return null|string
     * @throws GeneratorNotReadyException
     */
    public function generateAllReport(): ?string
    {
        $allUsers = $this
            ->entityManager
            ->getRepository(User::class)
            ->findAll()
        ;

        return $this->createReportForUserList($allUsers);
    }

    /**
     * @param Collection|User[]|array $userList
     *
     * @return string|null null if report was not created
     * @throws GeneratorNotReadyException due to invalid month (not in range 1-12)
     * @throws InvalidArgumentException Invalid file directory or name.
     */
    private function createReportForUserList($userList): ?string
    {
        if (empty($this->dateRange)) {
            throw new GeneratorNotReadyException('Invalid month');
        }

        $this->initializeZipArchive();

        $reportMonth = current($this->dateRange)->format('F');
        $currentDate = new DateTime();
        foreach ($userList as $user) {
            $csvData = $this->getUserTimesheetDaysAsCsv($user);
            if ($csvData) {
                $fileName = sprintf(
                    '%s_%s_%s.csv',
                    $user->getUsername(),
                    $reportMonth,
                    $currentDate->format('Y-m-d_H:i:s')
                );

                $this->zipArchive->addFromString(
                    $fileName,
                    $csvData
                );
            }
        }

        try {
            $this->zipArchive->close();
        } catch (ErrorException $exception) {
            if (strpos($exception->getMessage(), 'Failure to create temporary file')) {
                throw new InvalidArgumentException('Invalid file directory or name.');
            }

            unset($this->zipArchive);

            return null;
        }

        return $this->fullFilePath;
    }

    /**
     * Returns user's TimesheetDays data as csv string.
     *
     * @param User $user
     *
     * @return string|null
     */
    public function getUserTimesheetDaysAsCsv(User $user): ?string
    {
        [$rangeStart, $rangeEnd] = $this->dateRange;
        $userData = $this
           ->entityManager
           ->getRepository(UserTimesheetDay::class)
           ->findTimesheetDaysBetweenDate(
               $user,
               $rangeStart->format('Y-m-d'),
               $rangeEnd->format('Y-m-d'),
               UserTimesheetDayRepository::RETURN_AS_REPORT_ARRAY
           )
        ;

        if (!empty($userData)) {
            $writer = Writer::createFromStream(tmpfile());
            $writer->insertAll($userData);

            return $writer->getContent();
        }

        return null;
    }
}
