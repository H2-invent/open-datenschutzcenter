<?php

namespace App\Repository;

use App\Entity\Team;
use App\Entity\Vorfall;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Vorfall|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vorfall|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vorfall[]    findAll()
 * @method Vorfall[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VorfallRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vorfall::class);
    }

    // /**
    //  * @return Vorfall[] Returns an array of Vorfall objects
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
    public function findOneBySomeField($value): ?Vorfall
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findAllByTeam(Team $value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.team = :val AND a.activ = 1')
            ->setParameter('val', $value)
            ->orderBy('a.datum', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }
}
