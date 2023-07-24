<?php

namespace App\Repository;

use App\Entity\Datenweitergabe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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

    /**
     * @param array $teamPath
     * @return int|mixed|string
     * find transfers of type Datenweitergabe
     */
    public function findActiveTransfersByTeamPath(array $teamPath): mixed
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.team IN (:teamPath)')
            ->andWhere('a.activ = 1')
            ->andWhere('a.art = 1')
            ->setParameter('teamPath', $teamPath)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param array $teamPath
     * @return int|mixed|string
     * find transfers of type Auftragsverarbeitung
     */
    public function findActiveOrderProcessingsByTeamPath(array $teamPath): mixed
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.team IN (:teamPath)')
            ->andWhere('a.activ = 1')
            ->andWhere('a.art = 2')
            ->setParameter('teamPath', $teamPath)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findActiveByTeamAndUser($team, $user)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.team = :team')
            ->andWhere('a.assignedUser = :user')
            ->andWhere('a.activ = 1')
            ->setParameter('team', $team)
            ->setParameter('user', $user)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
