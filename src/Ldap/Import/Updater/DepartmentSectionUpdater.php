<?php declare(strict_types=1);

namespace App\Ldap\Import\Updater;

use App\Ldap\Constants\UserAttributes;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Department;
use App\Entity\Section;
use Countable;
use LdapTools\Object\LdapObject;
use InvalidArgumentException;

/**
 * Class DepartmentSectionUpdater
 */
final class DepartmentSectionUpdater extends AbstractUpdater
{
    /**
     * @var Countable
     */
    private $usersList;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var array
     */
    private $departmentSectionArray = [];

    /**
     * @param Countable $usersList
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(Countable $usersList, EntityManagerInterface $entityManager)
    {
        $this->usersList = $usersList;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function update(): void
    {
        $this->extractDepartmentSection();
        $this->write();
    }

    /**
     * Create department if not exists.
     *
     * @param string $departmentName
     * @param string $departmentShortName
     *
     * @return Department
     */
    private function createDepartmentIfNotExists(string $departmentName, string $departmentShortName): Department
    {
        $department = $this
            ->entityManager
            ->getRepository(Department::class)
            ->findOneBy([
                'name' => $departmentName
            ]);

        if (null === $department) {
            $department = new Department();
            $department
                ->setName($departmentName)
                ->setShortName($departmentShortName)
                ->setActive(true)
            ;
            $this
                ->entityManager
                ->persist($department)
            ;
        }

        return $department;
    }

    /**
     * Create section if not exists.
     *
     * @param string $sectionName
     *
     * @return Section
     */
    private function createSectionIfNotExists(string $sectionName): Section
    {
        $section = $this
            ->entityManager
            ->getRepository(Section::class)
            ->findOneBy([
                'name' => $sectionName
            ]);

        if (null === $section) {
            $section = new Section();
            $section
                ->setName($sectionName)
                ->setActive(true)
            ;
            $this
                ->entityManager
                ->persist($section)
            ;
        }

        return $section;
    }

    /**
     * Assigns section to department.
     *
     * @param Department $department
     * @param array|Section[]
     *
     * @return void
     */
    private function assignSectionsToDepartment(Department $department, array $sections): void
    {
        foreach ($sections as $section) {
            if (!$section instanceof Section) {
                $section = $this->createSectionIfNotExists($section);
            }

            $department->addSection($section);
        }
    }

    /**
     * Writes updates to database.
     *
     * @return void
     */
    private function write(): void
    {
        foreach ($this->departmentSectionArray as $departmentName => $departmentData) {
            $department = $this->createDepartmentIfNotExists($departmentName, $departmentData['short_name']);
            $this->assignSectionsToDepartment($department, $departmentData['sections']);

            $this->countSuccess();
        }

        $this->entityManager->flush();
    }

    /**
     * Extract sections and departments from LdapObject instance.
     * Matches sections to department.
     *
     * @return void
     */
    private function extractDepartmentSection(): void
    {
        $departmentSections = [];
        foreach ($this->usersList as $user) {
            if (! $user instanceof LdapObject) {
                throw new InvalidArgumentException('Instance of LdapObject expected.');
            }
            $userDepartment = $user->get(UserAttributes::DEPARTMENT);
            $userSection = $user->get(UserAttributes::SECTION);

            if (empty($userDepartment)) {
                continue;
            }

            if (!isset($departmentSections[$userDepartment])) {
                $departmentSections[$userDepartment]['short_name'] = $user->get(UserAttributes::DEPARTMENT_SHORT);
                $departmentSections[$userDepartment]['sections'] = [];
            }

            if (!in_array($userSection, $departmentSections[$userDepartment]['sections'], true) && $userSection) {
                $departmentSections[$userDepartment]['sections'][] = $userSection;
            }
        }

        $this->departmentSectionArray = $departmentSections;
    }
}
