<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 06.06.2020
 * Time: 19:01
 */

namespace App\Service;


use App\Entity\Task;
use App\Entity\Team;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;


class TaskService
{
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    function newTask(Team $team, User $user)
    {
        $tom = new Task();
        $tom->setTeam($team);
        $tom->setActiv(true);
        $tom->setCreatedAt(new \DateTime());
        $tom->setUser($user);

        return $tom;
    }
}
