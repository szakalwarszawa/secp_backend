<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserTimesheetDay;
use App\Utils\UserUtil;
use App\Utils\UserUtilsInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class UserOwnTimesheetDayAction
 */
class UserOwnTimesheetDayAction
{
    /**
     * @var UserUtil|null
     */
    private $userUtil;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * UserMe constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param UserUtilsInterface $userUtil
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UserUtilsInterface $userUtil
    ) {
        $this->userUtil = $userUtil;
        $this->entityManager = $entityManager;
    }

    /**
     * @param int $userId
     * @param string $dateFrom
     * @param string $dateTo
     *
     * @return UserTimesheetDay[]
     */
    public function __invoke(int $userId, string $dateFrom, string $dateTo): array
    {
        $currentUser =  $userId
            ? $this->entityManager->getRepository(User::class)->find($userId)
            : $this->userUtil->getCurrentUser();

        return $this
            ->entityManager
            ->getRepository(UserTimesheetDay::class)
            ->findWorkDayBetweenDate($currentUser, $dateFrom, $dateTo)
        ;
    }
}
