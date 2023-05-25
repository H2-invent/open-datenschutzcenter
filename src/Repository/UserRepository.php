<?php

namespace App\Repository;

use App\Entity\Team;
use App\Entity\User;
use App\Entity\Vorfall;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findAllByTeam(Team $value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.team = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult()
            ;
    }
}
