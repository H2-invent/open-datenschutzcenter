<?php

namespace App\Repository;

use App\Entity\Policies;
use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Policies|null find($id, $lockMode = null, $lockVersion = null)
 * @method Policies|null findOneBy(array $criteria, array $orderBy = null)
 * @method Policies[]    findAll()
 * @method Policies[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PoliciesRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly TeamRepository $teamRepository,
    )
    {
        parent::__construct($registry, Policies::class);
    }

    public function findActiveByTeam(Team $team)
    {
        $queryBuilder = $this->getBaseQueryBuilder(team: $team);
        $this->excludeIgnored(team: $team, queryBuilder: $queryBuilder);
        return $queryBuilder->getQuery()->getResult();
    }

    public function findAllByTeam(Team $team)
    {
        $queryBuilder = $this->getBaseQueryBuilder(team: $team);
        return $queryBuilder->getQuery()->getResult();
    }

    public function findPublicByTeam(Team $team)
    {
        $queryBuilder = $this->getBaseQueryBuilder(team: $team);
        $this->excludeIgnored(team: $team, queryBuilder: $queryBuilder);
        $queryBuilder->andWhere('a.status != 4');
        return $queryBuilder->getQuery()->getResult();
    }

    public function findActiveByTeamAndUser($team, $user)
    {
        $queryBuilder = $this->getBaseQueryBuilder(team: $team);
        $this->excludeIgnored(team: $team, queryBuilder: $queryBuilder);
        $queryBuilder
            ->andWhere('a.assignedUser = :user')
            ->setParameter('user', $user)
        ;
        return $queryBuilder->getQuery()->getResult();
    }

    private function excludeIgnored(Team $team, QueryBuilder $queryBuilder) :void
    {
        $ignored = $team->getIgnoredInheritances();
        if (count($ignored)) {
            $queryBuilder
                ->andWhere('process NOT IN (:ignored)')
                ->setParameter('ignored', $ignored);
        }
    }

    private function getBaseQueryBuilder(Team $team) :QueryBuilder
    {
        $teamPath = $this->teamRepository->getPath($team);

        return $this->createQueryBuilder('a')
            ->innerJoin('a.processes', 'process')
            ->andWhere('a.team = :team OR (process.team = :team OR process.inherited = 1) AND process.activ = 1 AND process.team IN (:teamPath)')
            ->andWhere('a.activ = 1')
            ->setParameter('teamPath', $teamPath)
            ->setParameter('team', $team)
            ->orderBy('a.createdAt', 'DESC')
        ;
    }
}
