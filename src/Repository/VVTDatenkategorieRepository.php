<?php

/**
 * Modified by
 * User: Jan Juister
 * Date: 13.05.2022
 */

namespace App\Repository;

use App\Entity\VVTDatenkategorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method VVTDatenkategorie|null find($id, $lockMode = null, $lockVersion = null)
 * @method VVTDatenkategorie|null findOneBy(array $criteria, array $orderBy = null)
 * @method VVTDatenkategorie[]    findAll()
 * @method VVTDatenkategorie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VVTDatenkategorieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VVTDatenkategorie::class);
    }

    // /**
    //  * @return VVTDatenkategorie[] Returns an array of VVTDatenkategorie objects
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
    public function findOneBySomeField($value): ?VVTDatenkategorie
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
            ->where('a.team = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult();
    }
}
