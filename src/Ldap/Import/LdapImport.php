<?php

declare(strict_types=1);

namespace App\Ldap\Import;

use App\Ldap\Constants\ImportResources;
use App\Ldap\Event\LdapImportedEvent;
use App\Ldap\Fetch\UsersFetcher;
use App\Ldap\Import\Updater\DepartmentSectionUpdater;
use App\Ldap\Import\Updater\UserUpdater;
use App\Ldap\Utils\PropertyRoleMatcher;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

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
     * @var null|StopwatchEvent
     */
    private $stopwatchResult = null;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var PropertyRoleMatcher
     */
    private $propertyRoleMatcher;

    /**
     * @param UsersFetcher $usersFetcher
     * @param EntityManagerInterface $entityManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param PropertyRoleMatcher $propertyRoleMatcher
     */
    public function __construct(
        UsersFetcher $usersFetcher,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        PropertyRoleMatcher $propertyRoleMatcher
    ) {
        $this->usersFetcher = $usersFetcher;
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->propertyRoleMatcher = $propertyRoleMatcher;
    }

    /**
     * Import data from AD to database.
     * Actions order is important.
     * Section/department --> Users
     * Departments and section are extracted from user's object.
     *
     * @param int $importResources
     *
     * @return ArrayCollection
     */
    public function import(int $importResources = ImportResources::IMPORT_ALL): ArrayCollection
    {
        $stopwatch = new Stopwatch(true);
        $stopwatch->start('ldapImport');

        $usersData = $this
            ->usersFetcher
            ->fetch()
        ;

        $results = [];
        if (
            in_array(
                $importResources,
                [
                    ImportResources::IMPORT_ALL,
                    ImportResources::IMPORT_DEPARTMENT_SECTION,
                ],
                true
            )
        ) {
            $departmentSectionUpdater = new DepartmentSectionUpdater($usersData, $this->entityManager);
            $departmentSectionUpdater->update();

            $stopwatch->lap('ldapImport');
            $results['department_section'] = $departmentSectionUpdater->getResultsCollector();
        }

        if (
            in_array(
                $importResources,
                [
                    ImportResources::IMPORT_ALL,
                    ImportResources::IMPORT_USERS,
                ],
                true
            )
        ) {
            $userUpdater = new UserUpdater($usersData, $this->entityManager, $this->propertyRoleMatcher);
            $userUpdater->update();

            $results['users'] = $userUpdater->getResultsCollector();
        }

        $this->stopwatchResult = $stopwatch->stop('ldapImport');
        $this
            ->eventDispatcher
            ->dispatch(new LdapImportedEvent($results), LdapImportedEvent::NAME)
        ;

        return ResponseFormatter::format($results);
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
}
