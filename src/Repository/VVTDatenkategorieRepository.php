<?php

/**
 * Modified by
 * User: Jan Juister
 * Date: 13.05.2022
 */

namespace App\Repository;

use App\Entity\Team;
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

    public function findByTeam(Team $team)
    {
        $teamPath = $this->teamRepository->getPath($team);

        $qb = $this->createQueryBuilder('a');
        return $qb
            ->andWhere('a.team is null OR a.team IN (:teamPath)')
            ->andWhere($qb->expr()->isNull('a.cloneOf'))
            ->andWhere('a.activ = 1')
            ->setParameter('teamPath', $teamPath)
            ->getQuery()
            ->getResult();
    }
}
