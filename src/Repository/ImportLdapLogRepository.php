<?php

namespace App\Repository;

use App\Entity\ImportLdapLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ImportLdapLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method ImportLdapLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method ImportLdapLog[]    findAll()
 * @method ImportLdapLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImportLdapLogRepository extends ServiceEntityRepository
{
    /**
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ImportLdapLog::class);
    }
}
