<?php

declare(strict_types=1);

namespace App\Ldap\Import\Updater;

use App\Ldap\Constants\UserAttributes;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Department;
use App\Entity\Section;
use LdapTools\Object\LdapObject;
use InvalidArgumentException;
use Traversable;
use App\Ldap\Import\Updater\Result\Result;
use App\Ldap\Import\Updater\Result\Types;
use App\Ldap\Import\Updater\Result\Actions;

/**
 * Class DepartmentSectionUpdater
 */
final class DepartmentSectionUpdater extends AbstractUpdater
{
    /**
     * @var Traversable
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
     * @param Traversable $usersList
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(Traversable $usersList, EntityManagerInterface $entityManager)
    {
        $this->usersList = $usersList;
        $this->entityManager = $entityManager;

        parent::__construct();
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

        $departmentNotExists = null === $department;
        if ($departmentNotExists) {
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

        $this->addResult(new Result(
            Department::class,
            Types::SUCCESS,
            $department->getName(),
            sprintf(
                'Department %s has been %s.',
                $department->getName(),
                $departmentNotExists ? 'created' : 'updated'
            ),
            $departmentNotExists ? Actions::CREATE : Actions::UPDATE
        ));

        return $department;
    }

    /**
     * Create section if not exists.
     *
     * @param string $sectionName
     * @param Department $department
     *
     * @return Section
     */
    private function createSectionIfNotExists(string $sectionName, Department $department): Section
    {
        $section = $this
            ->entityManager
            ->getRepository(Section::class)
            ->findOneBy([
                'name' => $sectionName,
                'department' => $department->getId(),
            ]);

        $sectionNotExists = null === $section;
        if ($sectionNotExists) {
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

        $this->addResult(new Result(
            Section::class,
            Types::SUCCESS,
            $section->getName(),
            sprintf(
                'Section %s has been %s.',
                $section->getName(),
                $sectionNotExists ? 'created' : 'updated'
            ),
            $sectionNotExists ? Actions::CREATE : Actions::UPDATE
        ));

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
                $section = $this->createSectionIfNotExists($section, $department);
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
        }

        $this->entityManager->flush();
    }

    /**
     * Extract sections and departments from LdapObject instance.
     * Matches sections to department.
     *
     * @throws InvalidArgumentException when object instance is not supported
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