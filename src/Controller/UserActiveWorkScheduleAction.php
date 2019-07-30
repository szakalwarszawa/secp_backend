<?php


namespace App\Controller;

use App\Entity\User;
use App\Entity\UserWorkScheduleDay;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class UserActiveWorkScheduleAction
 * @package App\Controller
 */
class UserActiveWorkScheduleAction extends AbstractController
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
     * @param TokenStorageInterface $tokenStorage
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage)
    {
        $this->token = $tokenStorage->getToken();
        $this->entityManager = $entityManager;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $currentUser = $this->token->getUser();
        /* @var $currentUser User */

        $userWorkSchedules = $this->entityManager->getRepository(UserWorkScheduleDay::class)
            ->findWorkDayBetweenDate($currentUser, '2019-05-01', '2019-08-31');

        return $this->json($userWorkSchedules);
    }
}
