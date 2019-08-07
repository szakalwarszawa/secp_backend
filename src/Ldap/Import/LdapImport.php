<?php declare(strict_types=1);

namespace App\Ldap\Import;

use App\Ldap\Fetch\UsersFetcher;
use Doctrine\ORM\EntityManagerInterface;
use App\Ldap\Import\Updater\DepartmentSectionUpdater;
use App\Ldap\Import\Updater\UserUpdater;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use App\Ldap\Constants\ImportResources;

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
     *
     * @return array
     */
    public function import(int $importResources = ImportResources::IMPORT_ALL): array
    {
        $usersData = $this
            ->usersFetcher
            ->fetch()
        ;

        $results = [];
        if (in_array($importResources, [
                ImportResources::IMPORT_ALL,
                ImportResources::IMPORT_DEPARTMENT_SECTION,
            ])) {
            $departmentSectionUpdater = new DepartmentSectionUpdater($usersData, $this->entityManager);
            $departmentSectionUpdater->update();
            $result = $departmentSectionUpdater->getCountAsString();
            $this
                ->logger
                ->log(LogLevel::INFO, 'Department/section import' . $result)
            ;

            $results['Department/section'] = $result;
        }

        if (in_array($importResources, [
                ImportResources::IMPORT_ALL,
                ImportResources::IMPORT_USERS,
            ])) {
            $userUpdater = new UserUpdater($usersData, $this->entityManager);
            $userUpdater->update();
            $result = $userUpdater->getCountAsString();
            $this
                ->logger
                ->log(LogLevel::INFO, 'Users import' . $result)
            ;

            $results['Users'] = $result;
        }

        return $results;
    }
}
