<?php

namespace App\Repository;

use App\Entity\AuditTomZiele;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AuditTomZiele|null find($id, $lockMode = null, $lockVersion = null)
 * @method AuditTomZiele|null findOneBy(array $criteria, array $orderBy = null)
 * @method AuditTomZiele[]    findAll()
 * @method AuditTomZiele[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuditTomZieleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuditTomZiele::class);
    }

    // /**
    //  * @return AuditTomZiele[] Returns an array of AuditTomZiele objects
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
    public function findOneBySomeField($value): ?AuditTomZiele
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
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
