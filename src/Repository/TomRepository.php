<?php

namespace App\Repository;

use App\Entity\Team;
use App\Entity\Tom;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Tom|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tom|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tom[]    findAll()
 * @method Tom[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TomRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly TeamRepository $teamRepository,
    )
    {
        parent::__construct($registry, Tom::class);
    }

    public function findActiveByTeam(Team $team)
    {
        $queryBuilder = $this->getBaseQueryBuilder($team);
        $this->excludeIgnored($team, $queryBuilder);
        return $queryBuilder->getQuery()->getResult();
    }

    public function findAllByTeam(Team $team)
    {
        $queryBuilder = $this->getBaseQueryBuilder($team);
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
            ->innerJoin('a.vvts', 'process')
            ->andWhere('a.team = :team OR process.inherited = 1 AND process.activ = 1 AND process.team IN (:teamPath)')
            ->andWhere('a.activ = 1')
            ->setParameter('teamPath', $teamPath)
            ->setParameter('team', $team)
            ->orderBy('a.createdAt', 'DESC')
            ;
    }
}
