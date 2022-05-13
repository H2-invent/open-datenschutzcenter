<?php

/**
 * Modified by
 * User: Jan Juister
 * Date: 13.05.2022
 */

namespace App\Repository;

use App\Entity\Loeschkonzept;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Loeschkonzept|null find($id, $lockMode = null, $lockVersion = null)
 * @method Loeschkonzept|null findOneBy(array $criteria, array $orderBy = null)
 * @method Loeschkonzept[]    findAll()
 * @method Loeschkonzept[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LoeschkonzeptRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Loeschkonzept::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Loeschkonzept $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Loeschkonzept $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return Loeschkonzept[] Returns an array of Loeschkonzept objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Loeschkonzept
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
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
