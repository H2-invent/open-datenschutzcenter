<?php

namespace App\Repository;

use App\Entity\VVTStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method VVTStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method VVTStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method VVTStatus[]    findAll()
 * @method VVTStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VVTStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VVTStatus::class);
    }

    // /**
    //  * @return VVTStatus[] Returns an array of VVTStatus objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?VVTStatus
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
