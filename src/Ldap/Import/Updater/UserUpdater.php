<?php declare(strict_types=1);

namespace App\Ldap\Import\Updater;

use LdapTools\Object\LdapObjectCollection;
use Doctrine\ORM\EntityManagerInterface;
use Countable;
use App\Entity\User;
use App\Ldap\Constants\UserAttributes;
use App\Entity\Department;
use LdapTools\Object\LdapObject;
use App\Entity\WorkScheduleProfile;
use App\Ldap\Import\Updater\AbstractUpdater;

/**
 * Class UserUpdater
 */
final class UserUpdater extends AbstractUpdater
{
    /**
     * @var string
     */
    const DEPARTMENT_MANAGER_POSITION = 'dyrektor';

    /**
     * @var string
     */
    const SECTION_MANAGER_POSITION = 'kierownik';

    /**
     * @var Countable
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
        foreach ($this->usersList as $user) {
            $this->createOrUpdateUser($user);
        }

        $this->entityManager->flush();
    }

    /**
     * Create or update user.
     * Additionally, it sets up section and department managers.
     *
     * @param LdapObject $userData
     *
     * @return bool
     */
    private function createOrUpdateUser(LdapObject $userData): bool
    {
        $user = $this
            ->entityManager
            ->getRepository(User::class)
            ->findOneBy([
                'username' => $userData->get(UserAttributes::SAMACCOUNTNAME)
            ])
        ;

        if (null === $user) {
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
            $this->countFail();

            return false;
        }

        $section = $department->getSectionByName($userData->get(UserAttributes::SECTION));

        $user
            ->setFirstName($userData->get('firstname'))
            ->setLastName($userData->get('lastname'))
            ->setEmail($userData->get('email'))
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

        $this->countSuccess();

        return true;
    }
}
