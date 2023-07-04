<?php

namespace App\Controller;

use App\Form\Type\TasksType;
use App\Repository\TaskRepository;
use App\Service\AssignService;
use App\Service\CurrentTeamService;
use App\Service\SecurityService;
use App\Service\TaskService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TaskController extends AbstractController
{


    public function __construct(private readonly TranslatorInterface $translator,
                                private EntityManagerInterface       $em,
    )
    {
    }

    #[Route(path: '/task/new', name: 'task_new')]
    public function addTask(
        ValidatorInterface $validator,
        Request            $request,
        SecurityService    $securityService,
        TaskService        $taskService,
        CurrentTeamService $currentTeamService,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
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
                $this->em->persist($data);
                $this->em->flush();

                return $this->redirectToRoute('tasks');
            }
        }
        return $this->render('task/new.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => $this->translator->trans(id: 'task.create', domain: 'task'),
            'task' => $task,
            'activ' => $task->getActiv(),
        ]);
    }

    #[Route(path: '/task/disable', name: 'task_disable')]
    public function disable(
        Request            $request,
        SecurityService    $securityService,
        CurrentTeamService $currentTeamService,
        TaskRepository     $taskRepository,
    ): Response
    {
        $user = $this->getUser();
        $team = $currentTeamService->getCurrentTeam($user);
        $task = $taskRepository->find($request->get('id'));

        if ($securityService->teamDataCheck($task, $team) && $securityService->adminCheck($user, $team)) {
            if ($task->getActiv() === 1) {
                $task->setActiv(2);
            } else {
                $task->setActiv(1);
            }

            $this->em->persist($task);
            $this->em->flush();
        }

        return $this->redirectToRoute('tasks');
    }

    #[Route(path: '/task/done', name: 'task_done')]
    public function done(
        Request            $request,
        SecurityService    $securityService,
        CurrentTeamService $currentTeamService,
        TaskRepository     $taskRepository,
    ): Response
    {
        $user = $this->getUser();
        $team = $currentTeamService->getCurrentTeam($user);
        $task = $taskRepository->find($request->get('id'));

        if ($securityService->teamDataCheck($task, $team) && $securityService->adminCheck($user, $team)) {
            if ($task->getActiv() === 1) {
                $task->setDone(1);
                $task->setDoneDate(new DateTime());
            }

            $this->em->persist($task);
            $this->em->flush();
        }

        return $this->redirectToRoute('tasks');
    }

    #[Route(path: '/task/edit', name: 'task_edit')]
    public function editTask(
        ValidatorInterface $validator,
        Request            $request,
        SecurityService    $securityService,
        AssignService      $assignService,
        CurrentTeamService $currentTeamService,
        TaskRepository     $taskRepository,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        $task = $taskRepository->find($request->get('id'));

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
                $this->em->persist($task);
                $this->em->flush();

                return $this->redirectToRoute(
                    'task_edit',
                    [
                        'id' => $task->getId(),
                        'snack' => $this->translator->trans(id: 'save.successful', domain: 'general'),
                    ],
                );
            }
        }
        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'assignForm' => $assign->createView(),
            'errors' => $errors,
            'title' => $this->translator->trans(id: 'task.edit', domain: 'task'),
            'task' => $task,
            'activ' => $task->getActiv(),
            'snack' => $request->get('snack'),
            'edit' => $request->get('edit'),
        ]);
    }

    #[Route(path: '/tasks', name: 'tasks')]
    public function index(
        SecurityService    $securityService,
        Request            $request,
        CurrentTeamService $currentTeamService,
        TaskRepository     $taskRepository,
    )
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        if ($request->get('all')) {
            $tasks = $taskRepository->findActiveByTeam($team);
            $all = true;
        } else {
            $tasks = $taskRepository->findBy(['team' => $team, 'activ' => true, 'done' => false]);
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
}
