<?php

namespace App\Repository;

use App\Entity\VVTDsfa;
use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method VVTDsfa|null find($id, $lockMode = null, $lockVersion = null)
 * @method VVTDsfa|null findOneBy(array $criteria, array $orderBy = null)
 * @method VVTDsfa[]    findAll()
 * @method VVTDsfa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VVTDsfaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VVTDsfa::class);
    }

    public function findAllByTeam(Team $team): array
    {
        return $this->createQueryBuilder('d')
            ->innerJoin('d.vvt', 'v')
            ->andWhere('v.team = :team')
            ->andWhere('d.activ = 1')
            ->andWhere('v.activ = 1')
            ->setParameter('team', $team)
            ->groupBy('d.vvt')
            ->orderBy('d.id', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findActiveByTeam($value)
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.vvt', 'v')
            ->andWhere('a.activ = 1')
            ->andWhere('v.team = :val')
            ->andWhere('v.activ = 1')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findActiveAndOpenByTeam($team) {
        return $this->createQueryBuilder('dsfa')
            ->innerJoin('dsfa.vvt', 'vvt')
            ->andWhere('vvt.activ = 1')
            ->andWhere('dsfa.activ = 1')
            ->andWhere('dsfa.dsb IS NULL OR dsfa.ergebnis IS NULL')
            ->andWhere('vvt.team = :team')
            ->setParameter('team', $team)
            ->getQuery()
            ->getResult();
    }

    public function findActiveByTeamAndUser($team, $user)
    {
        $query = $this->createQueryBuilder('dsfa')
            ->innerJoin('dsfa.vvt', 'vvt')
            ->andWhere('dsfa.assignedUser = :user')
            ->andWhere('vvt.team = :team')
            ->andWhere('vvt.activ = 1')
            ->andWhere('dsfa.activ = 1')
            ->setParameter('user', $user)
            ->setParameter('team', $team)
            ->getQuery()
            ->getResult();

        return $query;
    }
}
