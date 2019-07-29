<?php


namespace App\Controller;

use App\Entity\User;
use App\Entity\UserTimesheetDay;
use App\Entity\UserWorkScheduleDay;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use mysql_xdevapi\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Serializer\SerializerInterface;

class UserCreateTimesheetDayAction extends AbstractController
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
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     */
    public function __invoke($day, Request $request)
    {
        $dayDate = date('Y-m-d', strtotime($day));

        $userTimesheetDay = $this->serializer->deserialize($request->getContent(), UserTimesheetDay::class, 'json');
        /* @var $userTimesheetDay UserTimesheetDay */

        $userTimesheetDay->setDayDate($dayDate);
        $this->entityManager->persist($userTimesheetDay);
        $this->entityManager->flush();

        return $this->json($userTimesheetDay);
    }
}
