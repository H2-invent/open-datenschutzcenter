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
use DateTime;
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
        $task = new Task();
        $task->setTeam($team);
        $task->setActiv(true);
        $task->setCreatedAt(new DateTime());
        $task->setUser($user);
        $task->setDone(false);

        return $task;
    }
}
