<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Role|null find($id, $lockMode = null, $lockVersion = null)
 * @method Role|null findOneBy(array $criteria, array $orderBy = null)
 * @method Role[]    findAll()
 * @method Role[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoleRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }

    /**
     * Finds all roles and returns only thier names as array.
     *
     * @return Role[]
     */
    public function findAllAsSimpleArray(): array
    {
        $queryBuilder = $this->createQueryBuilder('r')
            ->select('r.name')
            ->getQuery();

        $result = $queryBuilder
            ->getArrayResult()
        ;

        return array_map(function ($element) {
            return $element['name'];
        }, $result);
    }
}
