<?php

namespace App\Repository;

use App\Entity\Produkte;
use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Produkte|null find($id, $lockMode = null, $lockVersion = null)
 * @method Produkte|null findOneBy(array $criteria, array $orderBy = null)
 * @method Produkte[]    findAll()
 * @method Produkte[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProdukteRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry                 $registry,
        private readonly TeamRepository $teamRepository,
    )
    {
        parent::__construct($registry, Produkte::class);
    }

    public function findActiveByTeam(Team $team)
    {
        $teamPath = $this->teamRepository->getPath($team);

        return $this->createQueryBuilder('a')
            ->andWhere('a.team IN (:teamPath)')
            ->andWhere('a.activ = 1')
            ->setParameter('teamPath', $teamPath)
            ->getQuery()
            ->getResult()
            ;
    }
}
