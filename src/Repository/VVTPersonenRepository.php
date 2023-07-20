<?php

namespace App\Repository;

use App\Entity\VVTPersonen;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method VVTPersonen|null find($id, $lockMode = null, $lockVersion = null)
 * @method VVTPersonen|null findOneBy(array $criteria, array $orderBy = null)
 * @method VVTPersonen[]    findAll()
 * @method VVTPersonen[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VVTPersonenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VVTPersonen::class);
    }

    // /**
    //  * @return VVTPersonen[] Returns an array of VVTPersonen objects
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
    public function findOneBySomeField($value): ?VVTPersonen
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findByTeam($value)
    {
        return $this->createQueryBuilder('a')
            ->where('a.team is null OR a.team = :val')
            ->andWhere('a.activ = 1')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult();
    }

    public function findByTeamPath(array $teamPath)
    {
        return $this->createQueryBuilder('a')
            ->where('a.team is null OR a.team IN (:teamPath)')
            ->andWhere('a.activ = 1')
            ->setParameter('teamPath', $teamPath)
            ->getQuery()
            ->getResult();
    }
}
