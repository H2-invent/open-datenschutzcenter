<?php

namespace App\Repository;

use App\Entity\AuditTomAbteilung;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AuditTomAbteilung|null find($id, $lockMode = null, $lockVersion = null)
 * @method AuditTomAbteilung|null findOneBy(array $criteria, array $orderBy = null)
 * @method AuditTomAbteilung[]    findAll()
 * @method AuditTomAbteilung[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuditTomAbteilungRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuditTomAbteilung::class);
    }

    // /**
    //  * @return AuditTomAbteilung[] Returns an array of AuditTomAbteilung objects
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
    public function findOneBySomeField($value): ?AuditTomAbteilung
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
            ->Where('a.team = :val')
            ->andWhere('a.activ = 1')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findActiveByTeamPath(array $teamPath)
    {
        return $this->createQueryBuilder('a')
            ->where('a.team IN (:teamPath)')
            ->andWhere('a.activ = 1')
            ->setParameter('teamPath', $teamPath)
            ->getQuery()
            ->getResult()
            ;
    }
}
