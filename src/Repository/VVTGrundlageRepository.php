<?php

namespace App\Repository;

use App\Entity\Team;
use App\Entity\VVTGrundlage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method VVTGrundlage|null find($id, $lockMode = null, $lockVersion = null)
 * @method VVTGrundlage|null findOneBy(array $criteria, array $orderBy = null)
 * @method VVTGrundlage[]    findAll()
 * @method VVTGrundlage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VVTGrundlageRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry                 $registry,
        private readonly TeamRepository $teamRepository,
    )
    {
        parent::__construct($registry, VVTGrundlage::class);
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
