<?php

namespace App\Repository;

use App\Entity\DatenweitergabeStand;
use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DatenweitergabeStand|null find($id, $lockMode = null, $lockVersion = null)
 * @method DatenweitergabeStand|null findOneBy(array $criteria, array $orderBy = null)
 * @method DatenweitergabeStand[]    findAll()
 * @method DatenweitergabeStand[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DatenweitergabeStandRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry                 $registry,
        private readonly TeamRepository $teamRepository,
    )
    {
        parent::__construct($registry, DatenweitergabeStand::class);
    }

    public function findActiveByTeam(Team $team)
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
