<?php

namespace App\Controller;

use App\Entity\UserTimesheetDay;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class UserCreateTimesheetDayAction
 * @package App\Controller
 */
class UserCreateTimesheetDayAction
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
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * UserMe constructor.
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface $tokenStorage
     * @param SerializerInterface $serializer
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        SerializerInterface $serializer
    ) {
        $this->token = $tokenStorage->getToken();
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    /**
     * @param string $day
     * @param Request $request
     * @return UserTimesheetDay
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function __invoke($day, Request $request): UserTimesheetDay
    {
        $dayDate = date('Y-m-d', strtotime($day));

        $userTimesheetDay = $this->serializer->deserialize($request->getContent(), UserTimesheetDay::class, 'json');
        /* @var $userTimesheetDay UserTimesheetDay */

        $userTimesheetDay->setDayDate($dayDate);
        $this->entityManager->persist($userTimesheetDay);
        $this->entityManager->flush();

        return $userTimesheetDay;
    }
}
