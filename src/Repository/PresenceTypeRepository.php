<?php

namespace App\Repository;

use App\Entity\PresenceType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PresenceType|null find($id, $lockMode = null, $lockVersion = null)
 * @method PresenceType|null findOneBy(array $criteria, array $orderBy = null)
 * @method PresenceType[]    findAll()
 * @method PresenceType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PresenceTypeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PresenceType::class);
    }
}
