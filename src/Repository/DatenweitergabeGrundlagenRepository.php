<?php

namespace App\Repository;

use App\Entity\DatenweitergabeGrundlagen;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DatenweitergabeGrundlagen|null find($id, $lockMode = null, $lockVersion = null)
 * @method DatenweitergabeGrundlagen|null findOneBy(array $criteria, array $orderBy = null)
 * @method DatenweitergabeGrundlagen[]    findAll()
 * @method DatenweitergabeGrundlagen[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DatenweitergabeGrundlagenRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry                 $registry,
        private readonly TeamRepository $teamRepository,
    )
    {
        parent::__construct($registry, DatenweitergabeGrundlagen::class);
    }

    public function findActiveByTeam($team)
    {
        $teamPath = $this->teamRepository->getPath($team);

        return $this->createQueryBuilder('a')
            ->where('a.team is null OR a.team IN (:teamPath)')
            ->andWhere('a.activ = 1')
            ->setParameter('teamPath', $teamPath)
            ->getQuery()
            ->getResult();
    }
}
