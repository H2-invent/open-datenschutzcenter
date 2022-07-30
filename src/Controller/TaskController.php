<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\Type\TasksType;
use App\Service\AssignService;
use App\Service\CurrentTeamService;
use App\Service\DisableService;
use App\Service\SecurityService;
use App\Service\TaskService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TaskController extends AbstractController
{
    /**
     * @Route("/tasks", name="tasks")
     */
    public function index(SecurityService $securityService, Request $request, CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        if ($request->get('all')) {
            $tasks = $this->getDoctrine()->getRepository(Task::class)->findActivByTeam($team);
            $all = true;
        } else {
            $tasks = $this->getDoctrine()->getRepository(Task::class)->findBy(['team' => $team, 'activ' => true, 'done' => false]);
            $all = false;
        }


        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('task/index.html.twig', [
            'task' => $tasks,
            'all' => $all,
            'currentTeam' => $team,
        ]);
    }

    /**
     * @Route("/task/new", name="task_new")
     */
    public function addTask(ValidatorInterface $validator, Request $request, SecurityService $securityService, TaskService $taskService, CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('tasks');
        }

        $task = $taskService->newTask($team, $this->getUser());

        $form = $this->createForm(TasksType::class, $task, ['user' => $team->getMembers()]);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $errors = $validator->validate($data);
            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($data);
                $em->flush();
                return $this->redirectToRoute('tasks');
            }
        }
        return $this->render('task/new.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'Aufgabe erstellen',
            'task' => $task,
            'activ' => $task->getActiv(),
        ]);
    }

    /**
     * @Route("/task/edit", name="task_edit")
     */
    public function EditTask(ValidatorInterface $validator, Request $request, SecurityService $securityService, AssignService $assignService, CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $task = $this->getDoctrine()->getRepository(Task::class)->find($request->get('id'));

        if ($securityService->teamDataCheck($task, $team) === false) {
            return $this->redirectToRoute('tasks');
        }

        $form = $this->createForm(TasksType::class, $task, ['user' => $team->getMembers()]);
        $form->handleRequest($request);
        $assign = $assignService->createForm($task, $team);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid() && $task->getActiv() === 1) {

            $task = $form->getData();
            $task->setUpdatedBy($this->getUser());
            $errors = $validator->validate($task);
            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($task);
                $em->flush();
                return $this->redirectToRoute('task_edit', ['id' => $task->getId(), 'snack' => 'Erfolgreich gepeichert']);
            }
        }
        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'assignForm' => $assign->createView(),
            'errors' => $errors,
            'title' => 'Aufgabe bearbeiten',
            'task' => $task,
            'activ' => $task->getActiv(),
            'snack' => $request->get('snack'),
            'edit' => $request->get('edit'),
        ]);
    }

    /**
     * @Route("/task/done", name="task_done")
     */
    public function done(Request $request, SecurityService $securityService)
    {
        $team = $this->getUser()->getAdminUser();
        $task = $this->getDoctrine()->getRepository(Task::class)->find($request->get('id'));

        if ($securityService->teamDataCheck($task, $team) === true) {
            if ($task->getActiv() === 1) {
                $task->setDone(1);
                $task->setDoneDate(new \DateTime());
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($task);
            $em->flush();
        }

        return $this->redirectToRoute('tasks');
    }

    /**
     * @Route("/task/disable", name="task_disable")
     */
    public function disable(Request $request, SecurityService $securityService)
    {
        $team = $this->getUser()->getAdminUser();
        $task = $this->getDoctrine()->getRepository(Task::class)->find($request->get('id'));

        if ($securityService->teamDataCheck($task, $team) === true) {
            if ($task->getActiv() === 1) {
                $task->setActiv(2);
            } else {
                $task->setActiv(1);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($task);
            $em->flush();
        }

        return $this->redirectToRoute('tasks');
    }
}
