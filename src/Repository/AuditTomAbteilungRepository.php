<?php

namespace App\Repository;

use App\Entity\AuditTomAbteilung;
use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AuditTomAbteilung|null find($id, $lockMode = null, $lockVersion = null)
 * @method AuditTomAbteilung|null findOneBy(array $criteria, array $orderBy = null)
 * @method AuditTomAbteilung[]    findAll()
 * @method AuditTomAbteilung[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuditTomAbteilungRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry                 $registry,
        private readonly TeamRepository $teamRepository,
    )
    {
        parent::__construct($registry, AuditTomAbteilung::class);
    }

    public function findActiveByTeam(Team $team)
    {
        $teamPath = $this->teamRepository->getPath($team);

        return $this->createQueryBuilder('a')
            ->where('a.team IN (:teamPath)')
            ->andWhere('a.activ = 1')
            ->setParameter('teamPath', $teamPath)
            ->getQuery()
            ->getResult()
            ;
    }
}
