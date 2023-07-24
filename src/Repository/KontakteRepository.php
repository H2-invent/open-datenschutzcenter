<?php

namespace App\Repository;

use App\Entity\Kontakte;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Kontakte|null find($id, $lockMode = null, $lockVersion = null)
 * @method Kontakte|null findOneBy(array $criteria, array $orderBy = null)
 * @method Kontakte[]    findAll()
 * @method Kontakte[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class KontakteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Kontakte::class);
    }

    // /**
    //  * @return Kontakte[] Returns an array of Kontakte objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('k')
            ->andWhere('k.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('k.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Kontakte
    {
        return $this->createQueryBuilder('k')
            ->andWhere('k.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findActiveByTeam($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.team = :val')
            ->andWhere('a.activ = 1')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findActiveByTeamPath(array $teamPath)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.team IN (:teamPath)')
            ->andWhere('a.activ = 1')
            ->setParameter('teamPath', $teamPath)
            ->getQuery()
            ->getResult()
            ;
    }
}
