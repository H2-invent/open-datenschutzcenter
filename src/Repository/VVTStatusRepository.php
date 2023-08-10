<?php

namespace App\Repository;

use App\Entity\Team;
use App\Entity\VVTStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method VVTStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method VVTStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method VVTStatus[]    findAll()
 * @method VVTStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VVTStatusRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry                 $registry,
        private readonly TeamRepository $teamRepository,
    )
    {
        parent::__construct($registry, VVTStatus::class);
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
