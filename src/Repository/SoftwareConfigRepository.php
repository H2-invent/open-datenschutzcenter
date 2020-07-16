<?php

namespace App\Repository;

use App\Entity\SoftwareConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SoftwareConfig|null find($id, $lockMode = null, $lockVersion = null)
 * @method SoftwareConfig|null findOneBy(array $criteria, array $orderBy = null)
 * @method SoftwareConfig[]    findAll()
 * @method SoftwareConfig[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SoftwareConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SoftwareConfig::class);
    }

    // /**
    //  * @return SoftwareConfig[] Returns an array of SoftwareConfig objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SoftwareConfig
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
