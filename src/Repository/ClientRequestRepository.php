<?php

namespace App\Repository;

use App\Entity\ClientRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ClientRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClientRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClientRequest[]    findAll()
 * @method ClientRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClientRequest::class);
    }

    // /**
    //  * @return ClientRequest[] Returns an array of ClientRequest objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ClientRequest
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
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
            ->getResult();
    }
}
