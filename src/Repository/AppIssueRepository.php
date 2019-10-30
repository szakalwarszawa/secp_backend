<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AppIssue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method AppIssue|null find($id, $lockMode = null, $lockVersion = null)
 * @method AppIssue|null findOneBy(array $criteria, array $orderBy = null)
 * @method AppIssue[]    findAll()
 * @method AppIssue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AppIssueRepository extends ServiceEntityRepository
{
    /**
     * AppIssueRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppIssue::class);
    }
}
