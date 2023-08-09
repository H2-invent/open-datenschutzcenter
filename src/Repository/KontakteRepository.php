<?php

namespace App\Repository;

use App\Entity\Kontakte;
use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Kontakte|null find($id, $lockMode = null, $lockVersion = null)
 * @method Kontakte|null findOneBy(array $criteria, array $orderBy = null)
 * @method Kontakte[]    findAll()
 * @method Kontakte[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class KontakteRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly TeamRepository $teamRepository,
    )
    {
        parent::__construct($registry, Kontakte::class);
    }

    public function findActiveByTeam(Team $team)
    {
        $queryBuilder = $this->getBaseQueryBuilder();
        $this->filterByTeam(queryBuilder: $queryBuilder, team: $team);
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
        return $this->createQueryBuilder('c')
            ->leftJoin('c.datenweitergaben', 'dw')
            ->leftJoin('dw.verfahren', 'process')
            ->andWhere('c.activ = 1')
            ;
    }

    private function filterByTeam(QueryBuilder $queryBuilder, Team $team, bool $all = false) :void
    {
        $teamPath = $this->teamRepository->getPath($team);
        $queryBuilder
            ->andWhere('c.team = :team OR (process.activ = 1 AND process.inherited = 1 AND process.team IN (:teamPath))')
            ->setParameter('teamPath', $teamPath)
            ->setParameter('team', $team)
        ;

        if (!$all) {
            $ignored = $team->getIgnoredInheritances();
            if (count($ignored)) {
                $queryBuilder
                    ->andWhere('process NOT IN (:ignored) OR c.team = :team')
                    ->setParameter('ignored', $ignored);
            }
        }
    }

    private function filterById(QueryBuilder $queryBuilder, string $id) :void
    {
        $queryBuilder
            ->andWhere('c.id = :id')
            ->setParameter('id', $id);
    }

    private function filterByInherited(QueryBuilder $queryBuilder) :void
    {
        $queryBuilder
            ->andWhere('process.activ = 1 AND process.inherited = 1');
    }
}
