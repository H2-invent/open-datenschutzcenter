<?php

namespace App\Repository;

use App\Entity\Software;
use App\Entity\Team;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\Boolean;

/**
 * @method Software|null find($id, $lockMode = null, $lockVersion = null)
 * @method Software|null findOneBy(array $criteria, array $orderBy = null)
 * @method Software[]    findAll()
 * @method Software[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SoftwareRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly TeamRepository $teamRepository,
    )
    {
        parent::__construct($registry, Software::class);
    }

    public function findActiveByTeam(Team $team)
    {
        $queryBuilder = $this->getBaseQueryBuilder();
        $this->filterByTeam(queryBuilder: $queryBuilder, team: $team);
        return $queryBuilder->getQuery()->getResult();
    }

    public function findActiveByTeamAndUser($team, $user)
    {
        $queryBuilder = $this->getBaseQueryBuilder();
        $this->filterByTeam(queryBuilder: $queryBuilder, team: $team);
        $this->filterByUser(queryBuilder: $queryBuilder, user: $user);
        return $queryBuilder->getQuery()->getResult();
    }

    public function findAllByTeam(Team $team)
    {
        $queryBuilder = $this->getBaseQueryBuilder();
        $this->filterByTeam(queryBuilder: $queryBuilder, team: $team, all: true);
        return $queryBuilder->getQuery()->getResult();
    }

    public function findIsInheritedById(string $id) : bool
    {
        $queryBuilder = $this->getBaseQueryBuilder();
        $this->filterById(queryBuilder: $queryBuilder, id: $id);
        $this->filterByInherited(queryBuilder: $queryBuilder);
        $result = $queryBuilder->getQuery()->getResult();
        return count($result) > 0;
    }

    public function findIsUsedByTeamAndId(Team $team, string $id) : bool
    {
        $queryBuilder = $this->getBaseQueryBuilder();
        $this->filterByTeam(queryBuilder: $queryBuilder, team: $team);
        $this->filterById(queryBuilder: $queryBuilder, id: $id);
        $result = $queryBuilder->getQuery()->getResult();
        return count($result) > 0;
    }

    private function getBaseQueryBuilder() :QueryBuilder
    {
        return $this->createQueryBuilder('sw')
            ->leftJoin('sw.vvts', 'sp')
            ->leftJoin('sw.datenweitergabe', 'dw')
            ->leftJoin('dw.verfahren', 'dp')
            ->andWhere('sw.activ = 1')
        ;
    }

    private function filterByTeam(QueryBuilder $queryBuilder, Team $team, bool $all = false) :void
    {
        $teamPath = $this->teamRepository->getPath($team);
        $queryBuilder
            ->andWhere('sw.team = :team OR (sp.activ = 1 AND sp.inherited = 1 AND sp.team IN (:teamPath)) OR (dp.activ = 1 AND dp.inherited = 1 AND dp.team IN (:teamPath))')
            ->setParameter('teamPath', $teamPath)
            ->setParameter('team', $team)
        ;

        if (!$all) {
            $ignored = $team->getIgnoredInheritances();
            if (count($ignored)) {
                $queryBuilder
                    ->andWhere('sp NOT IN (:ignored) OR dp NOT IN (:ignored) OR sw.team = :team')
                    ->setParameter('ignored', $ignored);
            }
        }
    }

    private function filterById(QueryBuilder $queryBuilder, string $id) :void
    {
        $queryBuilder
            ->andWhere('sw.id = :id')
            ->setParameter('id', $id);
    }

    private function filterByInherited(QueryBuilder $queryBuilder) :void
    {
        $queryBuilder
            ->andWhere('sp.activ = 1 AND sp.inherited = 1 OR dp.activ = 1 AND dp.inherited = 1');
    }

    private function filterByUser(QueryBuilder $queryBuilder, User $user) :void
    {
        $queryBuilder
            ->andWhere('sw.assignedUser = :user')
            ->setParameter('user', $user)
        ;
    }
}
