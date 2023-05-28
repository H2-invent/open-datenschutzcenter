<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function findActiveByTeamAndUser($team, $user)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.team = :team')
            ->andWhere('a.assignedUser = :user')
            ->andWhere('a.activ = 1')
            ->setParameter('team', $team)
            ->setParameter('user', $user)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findActiveByTeam($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.team = :val AND a.activ = 1')
            ->setParameter('val', $value)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findActiveAndOpenByTeam($team)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.team = :team')
            ->andWhere('a.activ = 1')
            ->andWhere('a.done = false')
            ->setParameter('team', $team)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findActiveByUser(User $user)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.assignedUser = :user')
            ->andWhere('a.done = 0')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}
