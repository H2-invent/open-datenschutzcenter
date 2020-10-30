<?php

namespace App\Repository;

use App\Entity\AkademieKurse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AkademieKurse|null find($id, $lockMode = null, $lockVersion = null)
 * @method AkademieKurse|null findOneBy(array $criteria, array $orderBy = null)
 * @method AkademieKurse[]    findAll()
 * @method AkademieKurse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AkademieKurseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AkademieKurse::class);
    }

    // /**
    //  * @return AkademieKurse[] Returns an array of AkademieKurse objects
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
    public function findOneBySomeField($value): ?AkademieKurse
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findKurseByTeam($value)
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.team' , 'team')
            ->andWhere('team = :team')
            ->andWhere('a.activ = 1')
            ->setParameter('team', $value)
            ->getQuery()
            ->getResult()
            ;
    }
}
