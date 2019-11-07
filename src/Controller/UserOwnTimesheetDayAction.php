<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserTimesheetDay;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class UserOwnTimesheetDayAction
 */
class UserOwnTimesheetDayAction
{
    /**
     * @var TokenInterface|null
     */
    private $token;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * UserMe constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->token = $tokenStorage->getToken();
        $this->entityManager = $entityManager;
    }

    /**
     * @param integer $userId
     * @param string $dateFrom
     * @param string $dateTo
     *
     * @return UserTimesheetDay[]
     */
    public function __invoke($userId, $dateFrom, $dateTo): array
    {
        $currentUser = $this->token->getUser();
        /* @var $currentUser User */
        if ($userId) {
            $currentUser = $this->entityManager
                ->getRepository(User::class)
                ->find((int)$userId)
            ;
        }

        $userTimesheetDays = $this->entityManager
            ->getRepository(UserTimesheetDay::class)
            ->findWorkDayBetweenDate($currentUser, $dateFrom, $dateTo)
        ;

        return $userTimesheetDays;
    }
}
