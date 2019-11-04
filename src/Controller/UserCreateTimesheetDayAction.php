<?php

namespace App\Controller;

use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Entity\UserTimesheetDay;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
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
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * UserMe constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface $tokenStorage
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $this->token = $tokenStorage->getToken();
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->validator = $validator;
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
        $this->validator->validate($userTimesheetDay);
        $this->entityManager->flush();

        return $userTimesheetDay;
    }
}
