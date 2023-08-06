<?php

namespace App\Repository;

use App\Entity\Team;
use App\Entity\VVTDsfa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method VVTDsfa|null find($id, $lockMode = null, $lockVersion = null)
 * @method VVTDsfa|null findOneBy(array $criteria, array $orderBy = null)
 * @method VVTDsfa[]    findAll()
 * @method VVTDsfa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VVTDsfaRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly TeamRepository $teamRepository,
    )
    {
        parent::__construct($registry, VVTDsfa::class);
    }

    public function findActiveByTeam(Team $team)
    {
        $queryBuilder = $this->getBaseQueryBuilder($team);
        return $queryBuilder->getQuery()->getResult();
    }

    public function findActiveAndOpenByTeam($team) {
        $queryBuilder = $this->getBaseQueryBuilder($team);
        $queryBuilder->andWhere('a.dsb IS NULL OR a.ergebnis IS NULL');
        return $queryBuilder->getQuery()->getResult();
    }

    public function findActiveByTeamAndUser($team, $user)
    {
        $queryBuilder = $this->getBaseQueryBuilder($team);
        $queryBuilder
            ->andWhere('a.assignedUser = :user')
            ->setParameter('user', $user);
        return $queryBuilder->getQuery()->getResult();
    }

    private function getBaseQueryBuilder(Team $team) :QueryBuilder
    {
        $teamPath = $this->teamRepository->getPath($team);
        $ignored = $team->getIgnoredInheritances();
        $queryBuilder = $this->createQueryBuilder('a')
            ->innerJoin('a.vvt', 'process')
            ->andWhere('process.activ = 1')
            ->andWhere('a.activ = 1')
            ->andWhere('process.team IN (:teamPath)')
            ->andWhere('process.team = :team OR process.inherited = 1')
            ->setParameter('teamPath', $teamPath)
            ->setParameter('team', $team)
            ;
        if (count($ignored)) {
            $queryBuilder
                ->andWhere('process NOT IN (:ignored)')
                ->setParameter('ignored', $ignored);
        }
        return $queryBuilder;
    }
}
