<?php

namespace App\Repository;

use App\Entity\VVTDsfa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method VVTDsfa|null find($id, $lockMode = null, $lockVersion = null)
 * @method VVTDsfa|null findOneBy(array $criteria, array $orderBy = null)
 * @method VVTDsfa[]    findAll()
 * @method VVTDsfa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VVTDsfaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VVTDsfa::class);
    }

    // /**
    //  * @return VVTDsfa[] Returns an array of VVTDsfa objects
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
    public function findOneBySomeField($value): ?VVTDsfa
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
            ->innerJoin('a.vvt', 'v')
            ->andWhere('a.activ = 1')
            ->andWhere('v.team = :val')
            ->andWhere('v.activ = 1')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult()
            ;
    }
}
