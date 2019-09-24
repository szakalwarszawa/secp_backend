<?php

declare(strict_types=1);

namespace App\Ldap\Utils;

use App\Entity\PropertyBasedRole;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use LdapTools\Object\LdapObject;
use InvalidArgumentException;

/**
 * Class PropertyRoleMatcher
 */
class PropertyRoleMatcher
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var LdapObject
     */
    private $baseLdapObject;

    /**
     * @var array
     */
    private $propertyBasedRoles;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        $this->propertyBasedRoles = $entityManager
            ->getRepository(PropertyBasedRole::class)
            ->findAll()
        ;
    }

    /**
     * @param LdapObject $baseLdapObject
     *
     * @return PropertyRoleMatcher
     */
    public function setBaseLdapObject(LdapObject $baseLdapObject): PropertyRoleMatcher
    {
        $this->baseLdapObject = $baseLdapObject;

        return $this;
    }

    /**
     * Adds property based roles to user.
     *
     * @param User $user
     *
     * @throws InvalidArgumentException when Ldap Object is not set.
     *
     * @return bool true if property based role has been added, false if no role has been added
     */
    public function addPropertyBasedRoles(User $user): bool
    {
        if (!$this->baseLdapObject) {
            throw new InvalidArgumentException('Ldap Object must be set.');
        }

        $this->cleanRoles($user);

        $propertyBasedUserRoles = $this->matchRolesToUser();
        $userRoles = $user->getRoles();

        $propertyBasedRolesAddedCount = 0;
        foreach ($propertyBasedUserRoles as $propertyBasedUserRole) {
            $userRoles[] = $propertyBasedUserRole->getRole();
            $propertyBasedRolesAddedCount++;
        }

        $user->setRoles(array_unique($userRoles));

        if ($propertyBasedRolesAddedCount) {
            return true;
        }

        return false;
    }

    /**
     * Removes all current user roles except not overridable and ROLE_USER|ROLE_ADMIN
     *
     * @param User $user
     *
     * @return void
     */
    private function cleanRoles(User $user): void
    {
        $notOverridableRoles = $this->findNotOverridableRoles();
        $notOverridableRoles[] = 'ROLE_USER';
        $notOverridableRoles[] = 'ROLE_ADMIN';
        $tempRolesArray = $user->getRoles();
        foreach ($tempRolesArray as $key => $role) {
            if (!in_array($role, $notOverridableRoles)) {
                unset($tempRolesArray[$key]);
            }
        }

        $user->setRoles($tempRolesArray);
    }

    /**
     * Finds not overridable roles.
     *
     * @return array|string[]
     */
    private function findNotOverridableRoles(): array
    {
        $propertyBasedRoles = $this->propertyBasedRoles;

        return array_filter(array_map(function ($propertyBasedRole) {
            if (!$propertyBasedRole->isOverridable()) {
                return $propertyBasedRole->getRole();
            }
        }, $propertyBasedRoles));
    }

    /**
     * Matches property based roles to current user ($this->baseLdapObject).
     *
     * @return array|PropertyBasedRole[]
     */
    private function matchRolesToUser(): array
    {
        $propertyBasedRoles = $this->propertyBasedRoles;

        return array_filter($propertyBasedRoles, function ($propertyBasedRole) {
            $property = $propertyBasedRole->getBasedOn();
            $userPropertyValue = $this
                ->baseLdapObject
                ->get($property)
            ;

            if ($userPropertyValue === $propertyBasedRole->getLdapValue()) {
                return $propertyBasedRole;
            }
        });
    }
}
