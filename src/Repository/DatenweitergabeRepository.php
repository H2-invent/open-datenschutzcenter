<?php

namespace App\Repository;

use App\Entity\Datenweitergabe;
use App\Entity\Team;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Datenweitergabe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Datenweitergabe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Datenweitergabe[]    findAll()
 * @method Datenweitergabe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DatenweitergabeRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly TeamRepository $teamRepository,
    )
    {
        parent::__construct($registry, Datenweitergabe::class);
    }

    public function findActiveByTeam(Team $team)
    {
        $queryBuilder = $this->getBaseQueryBuilder($team);
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param Team $team
     * @return int|mixed|string
     * find transfers of type Datenweitergabe
     */
    public function findActiveTransfersByTeam(Team $team): mixed
    {
        $queryBuilder = $this->getBaseQueryBuilder($team);
        $queryBuilder->andWhere('a.art = 1');
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param Team $team
     * @return int|mixed|string
     * find transfers of type Auftragsverarbeitung
     */
    public function findActiveOrderProcessingsByTeam(Team $team): mixed
    {
        $queryBuilder = $this->getBaseQueryBuilder($team);
        $queryBuilder->andWhere('a.art = 2');
        return $queryBuilder->getQuery()->getResult();
    }

    public function findActiveByTeamAndUser(Team $team, User $user)
    {
        $queryBuilder = $this->getBaseQueryBuilder($team);
        $queryBuilder
            ->andWhere('a.assignedUser = :user')
            ->setParameter('user', $user)
        ;
        return $queryBuilder->getQuery()->getResult();
    }

    private function getBaseQueryBuilder(Team $team) :QueryBuilder
    {
        $teamPath = $this->teamRepository->getPath($team);

        return $this->createQueryBuilder('a')
            ->innerJoin('a.verfahren', 'process')
            ->andWhere('a.team = :team OR process.inherited = 1 AND process.activ = 1 AND process.team IN (:teamPath)')
            ->andWhere('a.activ = 1')
            ->setParameter('teamPath', $teamPath)
            ->setParameter('team', $team)
            ->orderBy('a.createdAt', 'DESC')
            ;
    }
}
