<?php

namespace App\Repository;

use App\Entity\DatenweitergabeGrundlagen;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DatenweitergabeGrundlagen|null find($id, $lockMode = null, $lockVersion = null)
 * @method DatenweitergabeGrundlagen|null findOneBy(array $criteria, array $orderBy = null)
 * @method DatenweitergabeGrundlagen[]    findAll()
 * @method DatenweitergabeGrundlagen[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DatenweitergabeGrundlagenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DatenweitergabeGrundlagen::class);
    }

    // /**
    //  * @return DatenweitergabeGrundlagen[] Returns an array of DatenweitergabeGrundlagen objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DatenweitergabeGrundlagen
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
