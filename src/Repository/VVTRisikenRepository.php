<?php

namespace App\Repository;

use App\Entity\Team;
use App\Entity\VVTRisiken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method VVTRisiken|null find($id, $lockMode = null, $lockVersion = null)
 * @method VVTRisiken|null findOneBy(array $criteria, array $orderBy = null)
 * @method VVTRisiken[]    findAll()
 * @method VVTRisiken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VVTRisikenRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry                 $registry,
        private readonly TeamRepository $teamRepository,
    )
    {
        parent::__construct($registry, VVTRisiken::class);
    }

    public function findByTeam(Team $team)
    {
        $teamPath = $this->teamRepository->getPath($team);

        return $this->createQueryBuilder('a')
            ->where('a.team is null OR a.team IN (:teamIds)')
            ->andWhere('a.activ = 1')
            ->setParameter('teamIds', $teamPath)
            ->getQuery()
            ->getResult();
    }
}
