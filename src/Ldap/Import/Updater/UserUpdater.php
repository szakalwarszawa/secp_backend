<?php declare(strict_types=1);

namespace App\Ldap\Import\Updater;

use LdapTools\Object\LdapObjectCollection;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Ldap\Constants\UserAttributes;
use App\Entity\Department;
use LdapTools\Object\LdapObject;
use App\Entity\WorkScheduleProfile;
use App\Ldap\Import\Updater\AbstractUpdater;
use App\Ldap\Import\Updater\Result\Result;
use App\Ldap\Import\Updater\Result\Types;
use App\Ldap\Import\Updater\Result\Actions;
use Traversable;

/**
 * Class UserUpdater
 */
final class UserUpdater extends AbstractUpdater
{
    /**
     * @var string
     */
    public const DEPARTMENT_MANAGER_POSITION = 'dyrektor';

    /**
     * @var string
     */
    public const SECTION_MANAGER_POSITION = 'kierownik';

    /**
     * @var Traversable
     */
    private $usersList;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param LdapObjectCollection $usersList
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
        foreach ($this->usersList as $user) {
            $this->createOrUpdateUser($user);
        }

        $this->entityManager->flush();
    }

    /**
     * Create or update user.
     * Additionally, it sets up section and department managers.
     * If user has no first, last name or department defined it will be skipped.
     *
     * @param LdapObject $userData
     *
     * @return bool
     */
    private function createOrUpdateUser(LdapObject $userData): bool
    {
        $userFirstName = $userData->get(UserAttributes::FIRST_NAME);
        $userLastName = $userData->get(UserAttributes::LAST_NAME);

        if (!$userFirstName || !$userLastName) {
            $this->addResult(new Result(
                LdapObject::class,
                Types::FAIL,
                'LdapObject has no first name or last name',
                Actions::IGNORE
            ));

            return false;
        }

        $user = $this
            ->entityManager
            ->getRepository(User::class)
            ->findOneBy([
                'username' => $userData->get(UserAttributes::SAMACCOUNTNAME)
            ])
        ;

        $userNotExists = null === $user;
        if ($userNotExists) {
            $user = new User();
            $user
                ->setUsername($userData->get(UserAttributes::SAMACCOUNTNAME))
                ->setSamAccountName($userData->get(UserAttributes::SAMACCOUNTNAME))
                ->setRoles(['ROLE_USER'])
            ;

            $defaultWorkScheduleProfile = $this
                ->entityManager
                ->getRepository(WorkScheduleProfile::class)
                ->findOneBy([
                    'name' => 'Domyślny'
                ]);

            $user->setDefaultWorkScheduleProfile($defaultWorkScheduleProfile);
        }

        $department = $this
            ->entityManager
            ->getRepository(Department::class)
            ->findOneBy([
                'name' => $userData->get(UserAttributes::DEPARTMENT)
            ])
        ;

        if (null === $department) {
            $this->addResult(new Result(
                Department::class,
                Types::FAIL,
                sprintf('User`s %s department is null', $userData->get(UserAttributes::SAMACCOUNTNAME)),
                Actions::IGNORE
            ));

            return false;
        }

        $section = $department->getSectionByName($userData->get(UserAttributes::SECTION));

        $user
            ->setFirstName($userFirstName)
            ->setLastName($userLastName)
            ->setEmail($userData->get(UserAttributes::MAIL))
            ->setDepartment($department)
            ->setSection($section)
        ;

        $this->entityManager->persist($user);

        if (self::DEPARTMENT_MANAGER_POSITION === $userData->get(UserAttributes::POSITION)) {
            $department->addManager($user);

            $this->entityManager->persist($department);
        }

        if (self::SECTION_MANAGER_POSITION === $userData->get(UserAttributes::POSITION) && $section) {
            $section->addManager($user);

            $this->entityManager->persist($section);
        }

        $this->addResult(new Result(
            User::class,
            Types::SUCCESS,
            sprintf(
                'User %s has been %s.',
                $user->getUsername(),
                $userNotExists? 'created' : 'updated'
            ),
            $userNotExists? Actions::CREATE : Actions::UPDATE
        ));

        return true;
    }
}
