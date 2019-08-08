<?php declare(strict_types=1);

namespace App\Ldap\Import;

use App\Ldap\Fetch\UsersFetcher;
use Doctrine\ORM\EntityManagerInterface;
use App\Ldap\Import\Updater\DepartmentSectionUpdater;
use App\Ldap\Import\Updater\UserUpdater;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use App\Ldap\Constants\ImportResources;
use Symfony\Component\VarDumper\VarDumper;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param UsersFetcher $usersFetcher
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        UsersFetcher $usersFetcher,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ) {
        $this->usersFetcher = $usersFetcher;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * Import data from AD to database.
     * Actions order is important.
     * Section/department --> Users
     * Departments and section are extrated from user's object.
     *
     * @param int $importResources
     * @param bool $detailedReturn
     *
     * @return array
     */
    public function import(int $importResources = ImportResources::IMPORT_ALL, bool $detailedReturn = false): array
    {
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
            $this
                ->logger
                ->log(LogLevel::INFO, 'Department/section import' . $departmentSectionUpdater->getCountAsString())
            ;

            $results['department_section'] = $departmentSectionUpdater
                ->getResultsCollector()
                ->forceJoinFailures()
                ->getCounters()
            ;
        }

        if (in_array($importResources, [
                ImportResources::IMPORT_ALL,
                ImportResources::IMPORT_USERS,
            ], true)) {
            $userUpdater = new UserUpdater($usersData, $this->entityManager);
            $userUpdater->update();
            $result = $userUpdater->getCountAsString();
            $this
                ->logger
                ->log(LogLevel::INFO, 'Users import' . $userUpdater->getCountAsString())
            ;

            $results['users'] = $userUpdater
                ->getResultsCollector()
                ->forceJoinFailures()
                ->getCounters()
            ;
        }

        return $results;
    }
}
