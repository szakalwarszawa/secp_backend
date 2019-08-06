<?php declare(strict_types=1);

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
    const ACTIVE = 'active';

    /**
     * Fetch unactive users.
     *
     * @var string
     */
    const UNACTIVE = 'unactive';

    /**
     * Fetch all users.
     *
     * @var string
     */
    const ALL = 'all';

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
    private $unactiveUsersBaseDn;

    /**
     * @param LdapManager $ldapManager
     * @param string $usersBaseDn
     * @param string $unactiveUsersBaseDn
     */
    public function __construct(
        LdapManager $ldapManager,
        string $usersBaseDn,
        string $unactiveUsersBaseDn
    ) {
        $this->ldapManager = $ldapManager;
        $this->usersBaseDn = $usersBaseDn;
        $this->unactiveUsersBaseDn = $unactiveUsersBaseDn;
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
        $unactiveUsers = new ArrayCollection();

        if (in_array($type, [self::ALL, self::ACTIVE])) {
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

        if (in_array($type, [self::ALL, self::UNACTIVE])) {
            $unactiveUsers = $this
                ->ldapManager
                ->buildLdapQuery()
                ->setBaseDn($this->unactiveUsersBaseDn)
                ->select(UserAttributes::all())
                ->fromUsers()
                ->getLdapQuery()
                ->getResult()
            ;
        }

        $this->users = new ArrayCollection(array_merge($users->toArray(), $unactiveUsers->toArray()));

        return $this->users;
    }
}
