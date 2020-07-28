<?php

namespace App\Repository;

use App\Entity\Datenweitergabe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Datenweitergabe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Datenweitergabe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Datenweitergabe[]    findAll()
 * @method Datenweitergabe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DatenweitergabeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Datenweitergabe::class);
    }

    // /**
    //  * @return Datenweitergabe[] Returns an array of Datenweitergabe objects
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
    public function findOneBySomeField($value): ?Datenweitergabe
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
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
            ->getQuery()
            ->getResult()
            ;
    }
}
