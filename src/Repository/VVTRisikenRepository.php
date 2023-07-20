<?php

namespace App\Repository;

use App\Entity\VVTRisiken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method VVTRisiken|null find($id, $lockMode = null, $lockVersion = null)
 * @method VVTRisiken|null findOneBy(array $criteria, array $orderBy = null)
 * @method VVTRisiken[]    findAll()
 * @method VVTRisiken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VVTRisikenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VVTRisiken::class);
    }

    // /**
    //  * @return VVTRisiken[] Returns an array of VVTRisiken objects
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
    public function findOneBySomeField($value): ?VVTRisiken
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
            ->where('a.team is null OR a.team IN (:teamIds)')
            ->andWhere('a.activ = 1')
            ->setParameter('teamIds', $teamPath)
            ->getQuery()
            ->getResult();
    }
}
