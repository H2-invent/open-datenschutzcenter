<?php

namespace App\Repository;

use App\Entity\AuditTom;
use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AuditTom|null find($id, $lockMode = null, $lockVersion = null)
 * @method AuditTom|null findOneBy(array $criteria, array $orderBy = null)
 * @method AuditTom[]    findAll()
 * @method AuditTom[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuditTomRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuditTom::class);
    }

    // /**
    //  * @return AuditTom[] Returns an array of AuditTom objects
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
    public function findOneBySomeField($value): ?AuditTom
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findAllByTeam($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.team = :val AND a.activ = 1')
            ->setParameter('val', $value)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findAuditByTeam(Team $team)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.team = :team AND a.activ = 1')
            ->andWhere('a.status > 2')
            ->setParameter('team', $team)
            ->orderBy('a.nummer', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findOpenByTeam(Team $team)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.team = :val AND a.activ = 1')
            ->andWhere('a.status = 1')
            ->setParameter('val', $team)
            ->orderBy('a.nummer', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }
}
