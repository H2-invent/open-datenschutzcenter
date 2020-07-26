<?php

namespace App\Repository;

use App\Entity\AuditTomStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method AuditTomStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method AuditTomStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method AuditTomStatus[]    findAll()
 * @method AuditTomStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuditTomStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuditTomStatus::class);
    }

    // /**
    //  * @return AuditTomStatus[] Returns an array of AuditTomStatus objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AuditTomStatus
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
