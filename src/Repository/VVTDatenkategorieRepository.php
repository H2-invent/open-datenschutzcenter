<?php

/**
 * Modified by
 * User: Jan Juister
 * Date: 13.05.2022
 */

namespace App\Repository;

use App\Entity\Revisionable;
use App\Entity\Team;
use App\Entity\VVT;
use App\Entity\VVTDatenkategorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use function Doctrine\ORM\QueryBuilder;

/**
 * @method VVTDatenkategorie|null find($id, $lockMode = null, $lockVersion = null)
 * @method VVTDatenkategorie|null findOneBy(array $criteria, array $orderBy = null)
 * @method VVTDatenkategorie[]    findAll()
 * @method VVTDatenkategorie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VVTDatenkategorieRepository extends ServiceEntityRepository
{
    use InheritanceTrait;

    public function __construct(
        ManagerRegistry                 $registry,
        private readonly TeamRepository $teamRepository,
    )
    {
        parent::__construct($registry, VVTDatenkategorie::class);
    }

        /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(VVTDatenkategorie $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * Find all categories of current team or those inherited by parent teams,
     * if their vvt has enabled inheritance and inherited entities are not ignored.
     * Inherited entities are presented by their inactive clones, which might not be the latest version.
     * @param Team $team
     * @return VVTDatenkategorie[]
     */
    public function findActiveByTeam(Team $team): array
    {
        $teamPath = $this->teamRepository->getPath($team);
        $entityManager = $this->getEntityManager();

        return $entityManager->createQuery(
            'SELECT k
            FROM App\Entity\VVTDatenkategorie k
            LEFT JOIN k.team t
            LEFT JOIN k.vvts v
            WHERE (k.activ = 1 AND (k.team = :team OR k.team IS NULL))
            OR (v.inherited = 1 AND v.activ = 1 AND :team NOT MEMBER OF v.ignoredInTeams AND k.team IN (:teamPath) AND NOT k.team = :team)'
        )->setParameters([
            'team' => $team,
            'teamPath' => $teamPath
        ])->getResult();
    }

    /**
     * Find those categories, that are the latest version of the given team
     * or those that are inherited by ancestor team and inheritance is enabled by its vtt.
     * Also lists those inherited, which are ignored by given team.
     * Inherited entities are presented by their inactive clones, which might not be the latest version.
     * @param Team $team
     * @return VVTDatenkategorie[]
     */
    public function findAllByTeam(Team $team): array
    {
        $teamPath = $this->teamRepository->getPath($team);
        $entityManager = $this->getEntityManager();

        return $entityManager->createQuery(
            'SELECT k
            FROM App\Entity\VVTDatenkategorie k
            LEFT JOIN k.team t
            LEFT JOIN k.vvts v
            WHERE (k.activ = 1 AND (k.team = :team OR k.team IS NULL))
            OR (v.inherited = 1 AND v.activ = 1 AND k.team IN (:teamPath) AND NOT k.team = :team)'
        )->setParameters([
            'team' => $team,
            'teamPath' => $teamPath
        ])->getResult();
    }

    /**
     * @param Revisionable $clone
     * @return VVT|null
     */
    protected function getVvtByClone(Revisionable $clone): ?VVT
    {
        return $clone instanceof VVTDatenkategorie ? $clone->getVvts()->first() : null;
    }
}
