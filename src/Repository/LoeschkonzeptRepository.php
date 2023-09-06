<?php

/**
 * Modified by
 * User: Jan Juister
 * Date: 13.05.2022
 */

namespace App\Repository;

use App\Entity\Loeschkonzept;
use App\Entity\Revisionable;
use App\Entity\Team;
use App\Entity\VVT;
use App\Entity\VVTDatenkategorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\PersistentCollection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Loeschkonzept|null find($id, $lockMode = null, $lockVersion = null)
 * @method Loeschkonzept|null findOneBy(array $criteria, array $orderBy = null)
 * @method Loeschkonzept[]    findAll()
 * @method Loeschkonzept[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LoeschkonzeptRepository extends ServiceEntityRepository
{
    use InheritanceTrait;

    public function __construct(ManagerRegistry $registry, private TeamRepository $teamRepository)
    {
        parent::__construct($registry, Loeschkonzept::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Loeschkonzept $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Loeschkonzept $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * Find all categories of current team or those inherited by parent teams,
     * if their vvt has enabled inheritance and inherited entities are not ignored.
     * Inherited entities are presented by their inactive clones, which might not be the latest version.
     * @param Team $team
     * @return Loeschkonzept[]
     */
    public function findActiveByTeam(Team $team): array
    {
        $teamPath = $this->teamRepository->getPath($team);
        $entityManager = $this->getEntityManager();

        return $entityManager->createQuery(
            'SELECT l
            FROM App\Entity\Loeschkonzept l
            LEFT JOIN l.team t
            LEFT JOIN l.vvtdatenkategories k
            LEFT JOIN k.vvts v
            WHERE (l.activ = 1 AND (l.team = :team OR l.team IS NULL))
            OR (v.inherited = 1 AND v.activ = 1 AND :team NOT MEMBER OF v.ignoredInTeams AND l.team IN (:teamPath) AND NOT l.team = :team)'
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
     * @return Loeschkonzept[]
     */
    public function findAllByTeam(Team $team): array
    {
        $teamPath = $this->teamRepository->getPath($team);
        $entityManager = $this->getEntityManager();

        return $entityManager->createQuery(
            'SELECT l
            FROM App\Entity\Loeschkonzept l
            LEFT JOIN l.team t
            LEFT JOIN l.vvtdatenkategories k
            LEFT JOIN k.vvts v
            WHERE (l.activ = true AND (l.team = :team OR l.team IS NULL))
            OR (v.inherited = 1 AND v.activ = 1 AND l.team IN (:teamPath) AND NOT l.team = :team)'
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
        if ($clone instanceof Loeschkonzept) {
            /** @var VVTDatenkategorie $category */
            $category = $clone->getVvtdatenkategories()->first();
            return $category->getVvts()->first();
        }
        return null;
    }
}
