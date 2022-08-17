<?php

namespace App\Repository;

use App\Entity\AkademieBuchungen;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AkademieBuchungen|null find($id, $lockMode = null, $lockVersion = null)
 * @method AkademieBuchungen|null findOneBy(array $criteria, array $orderBy = null)
 * @method AkademieBuchungen[]    findAll()
 * @method AkademieBuchungen[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AkademieBuchungenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AkademieBuchungen::class);
    }

    public function findMyBuchungenByUser($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.user = :user')
            ->setParameter('user', $value)
            ->getQuery()
            ->getResult();
    }

    public function findActiveByUser(User $user)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.user = :user')
            ->andWhere('a.abgeschlossen = 0')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function findBuchungenByTeam($team, $kurs)
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.user', 'user')
            ->andWhere('user.akademieUser = :team')
            ->andWhere('a.kurs = :kurs')
            ->andWhere('a.abgeschlossen = 0')
            ->setParameter('team', $team)
            ->setParameter('kurs', $kurs)
            ->getQuery()
            ->getResult();
    }

    public function findBerichtByTeam($team)
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.user', 'user')
            ->andWhere('user.akademieUser = :team')
            ->setParameter('team', $team)
            ->getQuery()
            ->getResult();
    }

    public function findOffenByDate($day)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.abgeschlossen = 0')
            ->andWhere('a.zugewiesen < :datum')
            ->setParameter('datum', $day)
            ->getQuery()
            ->getResult();
    }
}
