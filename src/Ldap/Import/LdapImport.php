<?php

declare(strict_types=1);

namespace App\Ldap\Import;

use App\Ldap\Fetch\UsersFetcher;
use Doctrine\ORM\EntityManagerInterface;
use App\Ldap\Import\Updater\DepartmentSectionUpdater;
use App\Ldap\Import\Updater\UserUpdater;
use App\Ldap\Constants\ImportResources;
use App\Ldap\Constants\ArrayResponseFormats;
use App\Utils\ConstantsUtil;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;
use App\Entity\LdapImportLog;
use DateTime;
use App\Ldap\Event\LdapImportedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class LdapImport
 */
class LdapImport
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UsersFetcher
     */
    private $usersFetcher;

    /**
     * @var int
     */
    private $responseFormat = ArrayResponseFormats::COUNTER_SUCCESS_DETAILED_FAILED;

    /**
     * @var null|StopwatchEvent
     */
    private $stopwatchResult = null;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param UsersFetcher $usersFetcher
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        UsersFetcher $usersFetcher,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->usersFetcher = $usersFetcher;
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Import data from AD to database.
     * Actions order is important.
     * Section/department --> Users
     * Departments and section are extrated from user's object.
     *
     * @param int $importResources
     *
     * @return array
     */
    public function import(int $importResources = ImportResources::IMPORT_ALL): array
    {
        $stopwatch = new Stopwatch(true);
        $stopwatch->start('ldapImport');

        $usersData = $this
            ->usersFetcher
            ->fetch()
        ;

        $results = [];
        if (in_array($importResources, [
                ImportResources::IMPORT_ALL,
                ImportResources::IMPORT_DEPARTMENT_SECTION,
            ], true)) {
            $departmentSectionUpdater = new DepartmentSectionUpdater($usersData, $this->entityManager);
            $departmentSectionUpdater->update();

            $stopwatch->lap('ldapImport');
            $results['department_section'] = $departmentSectionUpdater->getResultsCollector();
        }

        if (in_array($importResources, [
                ImportResources::IMPORT_ALL,
                ImportResources::IMPORT_USERS,
            ], true)) {
            $userUpdater = new UserUpdater($usersData, $this->entityManager);
            $userUpdater->update();

            $results['users'] = $userUpdater->getResultsCollector();
        }

        $this->stopwatchResult = $stopwatch->stop('ldapImport');
        $this
            ->eventDispatcher
            ->dispatch(LdapImportedEvent::NAME, new LdapImportedEvent($results))
        ;

        return ResponseFormatter::format($results, $this->responseFormat);
    }

    /**
     * Returns StopwatchEvent instance.
     *
     * @return null|StopwatchEvent
     */
    public function getStopwatchResult(): ?StopwatchEvent
    {
        return $this->stopwatchResult;
    }

    /**
     * Set responseFormat
     *
     * @param int $responseFormat
     *
     * @return LdapImport
     */
    public function setResponseFormat(int $responseFormat): LdapImport
    {
        ConstantsUtil::constCheckValue($responseFormat, ArrayResponseFormats::class);
        $this->responseFormat = $responseFormat;

        return $this;
    }
}
