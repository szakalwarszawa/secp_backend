<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserWorkScheduleDay;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class OwnerActiveWorkScheduleRangeAction
 */
class OwnerActiveWorkScheduleRangeAction
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
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage)
    {
        $this->token = $tokenStorage->getToken();
        $this->entityManager = $entityManager;
    }

    /**
     * @param $dateFrom
     * @param $dateTo
     *
     * @return UserWorkScheduleDay[]
     */
    public function __invoke($dateFrom, $dateTo): array
    {
        $currentUser = $this->token->getUser();
        /* @var $currentUser User */

        $userWorkSchedules = $this->entityManager
            ->getRepository(UserWorkScheduleDay::class)
            ->findWorkDayBetweenDate($currentUser, $dateFrom, $dateTo)
        ;

        return $userWorkSchedules;
    }
}
