<?php

namespace App\Repository;

use App\Entity\VVT;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method VVT|null find($id, $lockMode = null, $lockVersion = null)
 * @method VVT|null findOneBy(array $criteria, array $orderBy = null)
 * @method VVT[]    findAll()
 * @method VVT[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VVTRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VVT::class);
    }

    // /**
    //  * @return VVT[] Returns an array of VVT objects
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
    public function findOneBySomeField($value): ?VVT
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findActivByTeam($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.team = :val')
            ->andWhere('a.activ = 1')
            ->setParameter('val', $value)
            ->orderBy('a.CreatedAt', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }
}
