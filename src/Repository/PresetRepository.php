<?php

namespace App\Repository;

use App\Entity\AuditTomZiele;
use App\Entity\DatenweitergabeGrundlagen;
use App\Entity\DatenweitergabeStand;
use App\Entity\Produkte;
use App\Entity\Team;
use App\Entity\VVTGrundlage;
use App\Entity\VVTPersonen;
use App\Entity\VVTRisiken;
use App\Entity\VVTStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method VVTStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method VVTStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method VVTStatus[]    findAll()
 * @method VVTStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
abstract class PresetRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry                   $registry,
        protected readonly TeamRepository $teamRepository,
        string                            $entityClass
    )
    {
        parent::__construct($registry, $entityClass);
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

    private function getBaseQueryBuilder() :QueryBuilder
    {
        return $this->createQueryBuilder('a')->andWhere('a.activ = 1');
    }

    private function filterByTeam(QueryBuilder $queryBuilder, Team $team, bool $all = false) :void
    {
        $teamPath = $this->teamRepository->getPath($team);
        $queryBuilder
            ->andWhere('a.team is null OR a.team = :team OR (a.team IN (:teamPath) AND a.inherited = 1)')
            ->setParameter('teamPath', $teamPath)
            ->setParameter('team', $team)
        ;

        if (!$all) {
            $ignored = $this->getIgnored($team);
            if (count($ignored)) {
                $queryBuilder
                    ->andWhere('a NOT IN (:ignored)')
                    ->setParameter('ignored', $ignored);
            }
        }
    }

    private function getIgnored(Team $team) {
        switch ($this->getEntityName()) {
            case VVTStatus::class:
                return $team->getIgnoredVVTStates();
            case VVTGrundlage::class:
                return $team->getIgnoredVVTGrounds();
            case VVTPersonen::class:
                return $team->getIgnoredVVTPersons();
            case VVTRisiken::class:
                return $team->getIgnoredVVTRisks();
            case AuditTomZiele::class:
                return $team->getIgnoredAuditGoals();
            case DatenweitergabeGrundlagen::class:
                return $team->getIgnoredDWGrounds();
            case DatenweitergabeStand::class:
                return $team->getIgnoredDwStates();
            case Produkte::class:
                return $team->getIgnoredProducts();
        }
    }
}
