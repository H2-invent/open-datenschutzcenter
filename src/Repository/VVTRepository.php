<?php

namespace App\Repository;

use App\Entity\Team;
use App\Entity\VVT;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method VVT|null find($id, $lockMode = null, $lockVersion = null)
 * @method VVT|null findOneBy(array $criteria, array $orderBy = null)
 * @method VVT[]    findAll()
 * @method VVT[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VVTRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly TeamRepository $teamRepository,
    )
    {
        parent::__construct($registry, VVT::class);
    }

    public function findActiveByTeam(Team $team)
    {
        $queryBuilder = $this->getBaseQueryBuilder($team);
        $this->excludeIgnored($team, $queryBuilder);
        return $queryBuilder->getQuery()->getResult();
    }

    // find current versions of vvts including inherited vvts which aren't used
    public function findAllByTeam(Team $team)
    {
        $queryBuilder = $this->getBaseQueryBuilder($team);
        return $queryBuilder->getQuery()->getResult();
    }

    public function findCriticalByTeam(Team $team) {
        $queryBuilder = $this->getBaseQueryBuilder($team);
        $this->excludeIgnored($team, $queryBuilder);
        $queryBuilder->andWhere('a.status = 3');
        return $queryBuilder->getQuery()->getResult();
    }

    public function findActiveByTeamAndUser($team, $user)
    {
        $queryBuilder = $this->getBaseQueryBuilder($team);
        $this->excludeIgnored($team, $queryBuilder);
        $queryBuilder
            ->andWhere('a.assignedUser = :user')
            ->setParameter('user', $user);

        return $queryBuilder->getQuery()->getResult();
    }

    private function excludeIgnored(Team $team, QueryBuilder $queryBuilder) :void
    {
        $ignored = $team->getIgnoredInheritances();
        if (count($ignored)) {
            $queryBuilder
                ->andWhere('a NOT IN (:ignored)')
                ->setParameter('ignored', $ignored);
        }
    }

    private function getBaseQueryBuilder(Team $team) :QueryBuilder
    {
        $teamPath = $this->teamRepository->getPath($team);

        return $this->createQueryBuilder('a')
            ->andWhere('a.team IN (:teamPath)')
            ->andWhere('a.team = :team OR a.inherited = 1')
            ->andWhere('a.activ = 1')
            ->setParameter('teamPath', $teamPath)
            ->setParameter('team', $team)
            ->orderBy('a.CreatedAt', 'DESC')
        ;
    }
}
