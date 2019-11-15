<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    /**
     * UserRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Finds system user.
     *
     * @return User
     * @throws EntityNotFoundException
     */
    public function findSystemUser(): User
    {
        $queryResult = $this
            ->createQueryBuilder('u')
            ->where('u.username = :systemUsername')
            ->setParameter('systemUsername', User::SYSTEM_USERNAME)
            ->getQuery()
            ->getOneOrNullResult()
        ;
        if (!$queryResult) {
            throw new EntityNotFoundException('There is no system user in database.');
        }

        return $queryResult;
    }
}
