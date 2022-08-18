<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 15.05.2020
 * Time: 09:15
 */

namespace App\Controller;

use App\Entity\AuditTomAbteilung;
use App\Entity\AuditTomZiele;
use App\Entity\Team;
use App\Form\Type\AbteilungType;
use App\Form\Type\NewType;
use App\Form\Type\TeamType;
use App\Repository\AuditTomAbteilungRepository;
use App\Repository\TeamRepository;;
use App\Service\SecurityService;
use App\Service\TeamService;
use App\Service\CurrentTeamService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TeamController extends AbstractController
{
    /**
     * @Route("/team_edit", name="team_edit")
     * @param ValidatorInterface $validator
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param SecurityService $securityService
     * @param CurrentTeamService $currentTeamService
     * @param TranslatorInterface $translator
     * @param TeamRepository $teamRepository
     * @return Response
     */
    public function edit(ValidatorInterface $validator,
                          Request $request,
                          EntityManagerInterface $em,
                          SecurityService $securityService,
                          CurrentTeamService $currentTeamService,
                          TranslatorInterface $translator,
                          TeamRepository $teamRepository
    ) : Response
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

        if (!$team || (!$securityService->adminCheck($user, $team))) {
            return $this->redirectToRoute('dashboard');
        }

        $goal = new AuditTomZiele();
        $goal->setTeam($team);
        $form = $this->createForm(TeamType::class, $team);
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
            'title' => $translator->trans('teamData')
        ]);
    }

    /**
     * @Route("/manage_teams", name="manage_teams")
     * @param SecurityService $securityService
     * @param TeamRepository $teamRepository
     * @return Response
     */
    public function manage(SecurityService $securityService,
                           TeamRepository  $teamRepository
    ) : Response
    {
        $user = $this->getUser();

        if (!$securityService->superAdminCheck($user)) {
            return $this->redirectToRoute('dashboard');
        }

        $teams = $teamRepository->findAll();

        return $this->render('team/manage.html.twig', [
            'teams' => $teams
        ]);
    }

    /**
     * @Route("/team_create", name="team_create")
     * @param ValidatorInterface $validator
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function create(ValidatorInterface $validator,
                           EntityManagerInterface $em,
                           Request $request,
                           TranslatorInterface $translator
    ) : Response
    {
        $user = $this->getUser();
        $team = new Team();
        $team->setActiv(true);
        $form = $this->createForm(TeamType::class, $team);
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
                return $this->redirectToRoute('dashboard');
            }
        }

        return $this->render('team/index.html.twig', [
            'controller_name' => 'TeamController',
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => $translator->trans('newTeam')
        ]);
    }

    /**
     * @Route("/team_custom", name="team_custom")
     * @param SecurityService $securityService
     * @param TeamService $teamService
     * @param CurrentTeamService $currentTeamService
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function customShow(SecurityService $securityService,
                               TeamService $teamService,
                               CurrentTeamService $currentTeamService,
                               TranslatorInterface $translator
    ) : Response
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
            'title' => $translator->trans('changeFormPresets'),
            'data' => $data,
            'edit' => false
        ]);
    }

    /**
     * @Route("/team_custom/create", name="team_custom_create")
     * @param Request $request
     * @param SecurityService $securityService
     * @param EntityManagerInterface $em
     * @param TeamService $teamService
     * @param ValidatorInterface $validator
     * @param CurrentTeamService $currentTeamService
     * @return Response
     */
    public function customCreate(Request $request,
                                 SecurityService $securityService,
                                 EntityManagerInterface $em,
                                 TeamService $teamService,
                                 ValidatorInterface $validator,
                                 CurrentTeamService $currentTeamService
    ) : Response
    {
        $user = $this->getUser();
        $team = $currentTeamService->getTeamFromSession($user);

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


    /**
     * @Route("/team_custom/deaktivieren", name="team_custom_deativate")
     * @param Request $request
     * @param SecurityService $securityService
     * @param EntityManagerInterface $em
     * @param TeamService $teamService
     * @param CurrentTeamService $currentTeamService
     * @return Response
     */
    public function customDeactivate(Request $request,
                                     SecurityService $securityService,
                                     EntityManagerInterface $em,
                                     TeamService $teamService,
                                     CurrentTeamService $currentTeamService
    ) : Response
    {
        $user = $this->getUser();
        $team = $currentTeamService->getTeamFromSession($user);

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


    /**
     * @Route("/team_abteilungen", name="team_abteilungen")
     * @param ValidatorInterface $validator
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param SecurityService $securityService
     * @param CurrentTeamService $currentTeamService
     * @param AuditTomAbteilungRepository $departmentRepository
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function abteilungenAdd(ValidatorInterface $validator,
                                   Request $request,
                                   EntityManagerInterface $em,
                                   SecurityService $securityService,
                                   CurrentTeamService $currentTeamService,
                                   AuditTomAbteilungRepository $departmentRepository,
                                   TranslatorInterface $translator
    ) : Response
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
            'title' => $translator->trans('departments'),
            'data' => $departments,
        ]);
    }

    /**
     * @Route("/team_abteilungen/deaktivieren", name="team_abteilungen_deativate")
     * @param Request $request
     * @param SecurityService $securityService
     * @param EntityManagerInterface $em
     * @param CurrentTeamService $currentTeamService
     * @param AuditTomAbteilungRepository $departmentRepository
     * @return Response
     */
    public function abteilungenRemove(Request $request,
                                      SecurityService $securityService,
                                      EntityManagerInterface $em,
                                      CurrentTeamService $currentTeamService,
                                      AuditTomAbteilungRepository $departmentRepository
    ) : Response
    {
        $user = $this->getUser();
        $team = $currentTeamService->getTeamFromSession($user);

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

    /**
     * @Route("/team/switch", name="team_switch")
     * @param Request $request
     * @param CurrentTeamService $userService
     * @return RedirectResponse
     */
    public function switchTeam(Request $request, CurrentTeamService $userService) : RedirectResponse
    {
        $team = $request->get('team');

        $userService->switchToTeam($team);

        return new RedirectResponse($request->headers->get('referer'));
    }
}
