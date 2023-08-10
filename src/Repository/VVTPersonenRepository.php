<?php

namespace App\Repository;

use App\Entity\Team;
use App\Entity\VVTPersonen;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method VVTPersonen|null find($id, $lockMode = null, $lockVersion = null)
 * @method VVTPersonen|null findOneBy(array $criteria, array $orderBy = null)
 * @method VVTPersonen[]    findAll()
 * @method VVTPersonen[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VVTPersonenRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry                 $registry,
        private readonly TeamRepository $teamRepository,
    )
    {
        parent::__construct($registry, VVTPersonen::class);
    }

    public function findByTeam(Team $team)
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
