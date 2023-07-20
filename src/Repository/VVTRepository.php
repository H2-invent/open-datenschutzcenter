<?php

namespace App\Repository;

use App\Entity\Team;
use App\Entity\VVT;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method VVT|null find($id, $lockMode = null, $lockVersion = null)
 * @method VVT|null findOneBy(array $criteria, array $orderBy = null)
 * @method VVT[]    findAll()
 * @method VVT[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VVTRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VVT::class);
    }

    public function findActiveByTeam($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.team = :val')
            ->andWhere('a.activ = 1')
            ->setParameter('val', $value)
            ->orderBy('a.CreatedAt', 'DESC')
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
            ->orderBy('a.CreatedAt', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findCriticalByTeam(Team $team) {
        return $this->createQueryBuilder('vvt')
            ->andWhere('vvt.team = :team')
            ->andWhere('vvt.activ = 1')
            ->andWhere('vvt.status = 3')
            ->orderBy('vvt.CreatedAt', 'DESC')
            ->setParameter('team', $team)
            ->getQuery()
            ->getResult();
    }

    public function findActiveByTeamAndUser($team, $user)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.team = :team')
            ->andWhere('a.assignedUser = :user')
            ->andWhere('a.activ = 1')
            ->setParameter('team', $team)
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}
