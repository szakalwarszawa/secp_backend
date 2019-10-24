<?php

declare(strict_types=1);

namespace App\Ldap\Fetch;

use LdapTools\LdapManager;
use App\Ldap\Constants\UserAttributes;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class UsersFetcher
 */
class UsersFetcher
{
    /**
     * Fetch active users.
     *
     * @var string
     */
    public const ACTIVE = 'active';

    /**
     * Fetch inactive users.
     *
     * @var string
     */
    public const INACTIVE = 'inactive';

    /**
     * Fetch all users.
     *
     * @var string
     */
    public const ALL = 'all';

    /**
     * @var LdapManager
     */
    private $ldapManager;

    /**
     * @var ArrayCollection
     */
    private $users;

    /**
     * @var string
     */
    private $usersBaseDn;

    /**
     * @var string
     */
    private $inactiveUsersBaseDn;

    /**
     * @param LdapManager $ldapManager
     * @param string $usersBaseDn
     * @param string $inactiveUsersBaseDn
     */
    public function __construct(
        LdapManager $ldapManager,
        string $usersBaseDn,
        string $inactiveUsersBaseDn
    ) {
        $this->ldapManager = $ldapManager;
        $this->usersBaseDn = $usersBaseDn;
        $this->inactiveUsersBaseDn = $inactiveUsersBaseDn;
        $this->users = new ArrayCollection();
    }

    /**
     * Fetch users by type.
     *
     * @param string $type
     *
     * @return ArrayCollection
     */
    public function fetch(string $type = self::ALL): ArrayCollection
    {
        $users = new ArrayCollection();
        $inactiveUsers = new ArrayCollection();

        if (in_array($type, [self::ALL, self::ACTIVE], true)) {
            $users = $this
                ->ldapManager
                ->buildLdapQuery()
                ->setBaseDn($this->usersBaseDn)
                ->select(UserAttributes::all())
                ->fromUsers()
                ->getLdapQuery()
                ->getResult()
            ;
        }

        if (in_array($type, [self::ALL, self::INACTIVE], true)) {
            $inactiveUsers = $this
                ->ldapManager
                ->buildLdapQuery()
                ->setBaseDn($this->inactiveUsersBaseDn)
                ->select(UserAttributes::all())
                ->fromUsers()
                ->getLdapQuery()
                ->getResult()
            ;
        }

        $this->users = new ArrayCollection(array_merge($users->toArray(), $inactiveUsers->toArray()));

        return $this->users;
    }
}
