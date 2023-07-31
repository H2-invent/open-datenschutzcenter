<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 15.05.2020
 * Time: 09:15
 */

namespace App\Controller;

use App\Entity\AuditTomAbteilung;
use App\Entity\Team;
use App\Form\Type\AbteilungType;
use App\Form\Type\DeleteTeamType;
use App\Form\Type\NewType;
use App\Form\Type\TeamType;
use App\Repository\AuditTomAbteilungRepository;
use App\Repository\SettingsRepository;
use App\Repository\TeamRepository;
use App\Service\CurrentTeamService;
use App\Service\SecurityService;
use App\Service\TeamService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TeamController extends AbstractController
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    #[Route(path: '/team_abteilungen', name: 'team_abteilungen')]
    public function abteilungenAdd(
        ValidatorInterface          $validator,
        Request                     $request,
        EntityManagerInterface      $em,
        SecurityService             $securityService,
        CurrentTeamService          $currentTeamService,
        AuditTomAbteilungRepository $departmentRepository,
    ): Response
    {
        $user = $this->getUser();
        $team = $currentTeamService->getCurrentAdminTeam($user);

        if (!$team || !$securityService->adminCheck($user, $team)) {
            return $this->redirectToRoute('dashboard');
        }

        $departments = $departmentRepository->findAllByTeam($team);

        if ($request->get('id')) {
            $department = $departmentRepository->find($request->get('id'));

        } else {
            $department = new AuditTomAbteilung();
            $department->setActiv(true);
            $department->setTeam($team);
        }

        $form = $this->createForm(AbteilungType::class, $department);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $errors = $validator->validate($data);
            if (count($errors) == 0) {
                $em->persist($data);
                $em->flush();
                return $this->redirectToRoute('team_abteilungen');
            }
        }
        return $this->render('team/abteilungen.html.twig', [
            'currentTeam' => $team,
            'adminArea' => true,
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => $this->translator->trans(id: 'departments', domain: 'general'),
            'data' => $departments,
        ]);
    }

    #[Route(path: '/team_abteilungen/deaktivieren', name: 'team_abteilungen_deativate')]
    public function abteilungenRemove(
        Request                     $request,
        SecurityService             $securityService,
        EntityManagerInterface      $em,
        CurrentTeamService          $currentTeamService,
        AuditTomAbteilungRepository $departmentRepository,
    ): Response
    {
        $user = $this->getUser();
        $team = $currentTeamService->getCurrentTeam($user);

        if ($securityService->adminCheck($user, $team) === false) {
            return $this->redirectToRoute('team_abteilungen');
        }

        $department = $departmentRepository->findOneBy(array('id' => $request->get('id')));
        if ($this->getUser()->hasTeam($department->getTeam())) {
            $department->setActiv(false);
        }

        $em->persist($department);
        $em->flush();
        return $this->redirectToRoute('team_abteilungen');
    }

    #[Route(path: '/team_create', name: 'team_create')]
    public function create(
        ValidatorInterface     $validator,
        EntityManagerInterface $em,
        Request                $request,
        SecurityService        $securityService,
        TeamRepository         $teamRepository,
    ): Response
    {
        $user = $this->getUser();

        if ($securityService->superAdminCheck($user) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $teams = $teamRepository->findAll();

        $team = new Team();
        $team->setActiv(true);
        $form = $this->createForm(
            TeamType::class,
            $team,
            ['teams' => $teams,]
        );
        $form->remove('video');
        $form->remove('externalLink');
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $nTeam = $form->getData();
            $errors = $validator->validate($nTeam);
            if (count($errors) == 0) {
                $user->addTeam($nTeam);
                $nTeam->addAdmin($user);
                $em->persist($nTeam);
                $em->persist($user);
                $em->flush();
                return $this->redirectToRoute('team_edit',['id'=>$nTeam->getId()]);
            }
        }

        return $this->render('team/index.html.twig', [
            'controller_name' => 'TeamController',
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => $this->translator->trans(id: 'team.create', domain: 'team')
        ]);
    }

    #[Route(path: '/team_custom/create', name: 'team_custom_create')]
    public function customCreate(
        Request                $request,
        SecurityService        $securityService,
        EntityManagerInterface $em,
        TeamService            $teamService,
        ValidatorInterface     $validator,
        CurrentTeamService     $currentTeamService,
    ): Response
    {
        $user = $this->getUser();
        $team = $currentTeamService->getCurrentTeam($user);

        if ($securityService->adminCheck($user, $team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $data1 = $teamService->create($request->get('type'), $request->get('id'), $team);

        $form = $this->createForm(NewType::class, $data1);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $errors = $validator->validate($data);
            if (count($errors) == 0) {
                $em->persist($data);
                $em->flush();
                return $this->redirect($this->generateUrl('team_custom') . '#' . $request->get('type'));
            }
        }

        return $this->render('team/modalView.html.twig', [
            'form' => $form->createView(),
            'title' => $request->get('title'),
            'type' => $request->get('type'),
            'id' => $request->get('id'),
            'errors' => $errors
        ]);
    }

    #[Route(path: '/team_custom/deaktivieren', name: 'team_custom_deativate')]
    public function customDeactivate(
        Request                $request,
        SecurityService        $securityService,
        EntityManagerInterface $em,
        TeamService            $teamService,
        CurrentTeamService     $currentTeamService,
    ): Response
    {
        $user = $this->getUser();
        $team = $currentTeamService->getCurrentTeam($user);

        if ($securityService->adminCheck($user, $team) === false) {
            return $this->redirectToRoute('team_custom');
        }

        $data = $teamService->delete($request->get('type'), $request->get('id'));

        if ($this->getUser()->hasTeam($data->getTeam())) {
            $data->setActiv(false);
        }

        $em->persist($data);
        $em->flush();

        return $this->redirectToRoute('team_custom');
    }

    #[Route(path: '/team_custom', name: 'team_custom')]
    public function customShow(
        SecurityService    $securityService,
        TeamService        $teamService,
        CurrentTeamService $currentTeamService,
    ): Response
    {
        $user = $this->getUser();
        $team = $currentTeamService->getCurrentAdminTeam($user);

        if (!$team || !$securityService->adminCheck($user, $team)) {
            return $this->redirectToRoute('dashboard');
        }

        $data = $teamService->show($team);

        return $this->render('team/custom.html.twig', [
            'currentTeam' => $team,
            'adminArea' => true,
            'title' => $this->translator->trans(id: 'customFormPresents', domain: 'team'),
            'data' => $data,
            'edit' => false
        ]);
    }

    #[Route(path: '/team_edit', name: 'team_edit')]
    public function edit(
        ValidatorInterface     $validator,
        Request                $request,
        EntityManagerInterface $em,
        SecurityService        $securityService,
        CurrentTeamService     $currentTeamService,
        TeamRepository         $teamRepository,
    ): Response
    {
        $user = $this->getUser();
        $teamId = $request->get('id');
        $currentTeam = null;

        if ($teamId) {
            $team = $teamRepository->find($teamId);
        } else {
            $team = $currentTeamService->getCurrentAdminTeam($user);
            $currentTeam = $team;
        }

        // only admins can edit
        if (!$team || (!$securityService->adminCheck($user, $team))) {
            return $this->redirectToRoute('dashboard');
        }

        $availableTeams = $currentTeamService->getTeamsWithoutCurrentHierarchy($user, $team);

        $form = $this->createForm(
            TeamType::class,
            $team,
            ['teams' => $availableTeams,]
        );
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $nTeam = $form->getData();
            $errors = $validator->validate($nTeam);
            if (count($errors) == 0) {
                $em->persist($nTeam);
                $em->flush();
                if ($teamId) {
                    return $this->redirectToRoute('team_edit', ['id' => $teamId]);
                }
                return $this->redirectToRoute('team_edit');
            }
        }
        return $this->render('team/index.html.twig', [
            'currentTeam' => $currentTeam,
            'adminArea' => true,
            'controller_name' => 'TeamController',
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => $this->translator->trans(id: 'team.data', domain: 'team')
        ]);
    }

    #[Route(path: '/manage_teams', name: 'manage_teams')]
    public function manage(
        SecurityService    $securityService,
        SettingsRepository $settingsRepository,
        TeamRepository     $teamRepository,
    ): Response
    {
        $user = $this->getUser();
        $settings = $settingsRepository->findOne();
        $useKeycloakGroups = $settings ? $settings->getUseKeycloakGroups() : false;

        if (!$securityService->superAdminCheck($user)) {
            return $this->redirectToRoute('dashboard');
        }

        $teams = $teamRepository->findAll();

        return $this->render('team/manage.html.twig', [
            'teams' => $teams,
            'useKeycloakGroups' => $useKeycloakGroups,
        ]);
    }

    #[Route(path: '/team/switch', name: 'team_switch')]
    public function switchTeam(
        Request               $request,
        CurrentTeamService    $userService,
        UrlGeneratorInterface $urlGenerator,
        TeamRepository        $teamRepository,
    ): RedirectResponse
    {
        $team = $request->get('team');
        if (is_numeric($team)){
            $teamTest = $teamRepository->find($team);
            if ($teamTest) {
                if (in_array($this->getUser(), $teamTest->getMembers()->toArray())) {
                    $userService->switchToTeam($team);
                }
            }

        }
        $referer = $request->headers->get('referer');
        return new RedirectResponse($referer ?: $urlGenerator->generate('dashboard'));
    }

    #[Route(path: '/manage_teams/delete', name: 'team_delete')]
    public function teamDelete(
        Request                $request,
        SecurityService        $securityService,
        EntityManagerInterface $em,
        TeamRepository         $teamRepository,
        CurrentTeamService     $currentTeamService,
    ): Response
    {
        $user = $this->getUser();
        $teamId = $request->get('id');
        $team = $teamId ? $teamRepository->find($teamId) : $currentTeamService->getCurrentAdminTeam($user);

        if ($securityService->superAdminCheck($user) === false) {
            return $this->redirectToRoute('dashboard');
        }

        if ($team->getDeleteBlockers()) {
            return $this->render('team/modalViewDeleteBlockers.html.twig', [
                'team' => $team,
                'type' => $request->get('type')
            ]);
        } else {
            $data = array();
            $form = $this->createForm(DeleteTeamType::class, $data);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                if ($data['teamName'] === $team->getName()) {
                    $em->remove($team);
                    $em->flush();
                }
                return $this->redirectToRoute('manage_teams');
            }

            return $this->render('team/modalViewDelete.html.twig', [
                'form' => $form->createView(),
                'team' => $team,
                'type' => $request->get('type')
            ]);
        }
    }
}
