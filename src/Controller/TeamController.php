<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 15.05.2020
 * Time: 09:15
 */

namespace App\Controller;

use App\Entity\AkademieKurse;
use App\Entity\AuditTomAbteilung;
use App\Entity\AuditTomZiele;
use App\Entity\User;
use App\Form\Type\AbteilungType;
use App\Form\Type\DsbType;
use App\Form\Type\NewMemberType;
use App\Form\Type\NewType;
use App\Form\Type\TeamType;
use App\Service\InviteService;
use App\Service\SecurityService;
use App\Service\TeamService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TeamController extends AbstractController
{
    /**
     * @Route("/team_edit", name="team_edit")
     */
    public function index(ValidatorInterface $validator, Request $request, SecurityService $securityService)
    {
        $team = $this->getUser()->getAdminUser();

        if (!$securityService->adminCheck($this->getUser(), $team)) {
            return $this->redirectToRoute('dashboard');
        }

        $ziel = new AuditTomZiele();
        $ziel->setTeam($team);
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $nTeam = $form->getData();
            $errors = $validator->validate($nTeam);
            if (count($errors) == 0) {
                $text = array();
                $em = $this->getDoctrine()->getManager();
                $em->persist($nTeam);
                $em->flush();
                return $this->redirectToRoute('team_edit');
            }
        }
        return $this->render('team/index.html.twig', [
            'controller_name' => 'TeamController',
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'Stammdaten'
        ]);
    }


    /**
     * @Route("/team_custom", name="team_custom")
     */
    public function customShow(ValidatorInterface $validator, Request $request, SecurityService $securityService, TeamService $teamService)
    {
        $team = $this->getUser()->getAdminUser();

        if ($securityService->adminCheck($this->getUser(), $team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $data = $teamService->show($team);

        return $this->render('team/custom.html.twig', [
            'title' => 'Vorgaben für Formulare anpassen',
            'data' => $data,
            'edit' => false
        ]);
    }

    /**
     * @Route("/akademie/admin", name="akademie_admin")
     */
    public function academyAdmin(ValidatorInterface $validator, Request $request, InviteService $inviteService, SecurityService $securityService)
    {
        $team = $this->getUser()->getAdminUser();

        // Admin Route only
        if (!$securityService->adminCheck($this->getUser(), $team)) {
            return $this->redirectToRoute('dashboard');
        }
        $kurse = $this->getDoctrine()->getRepository(AkademieKurse::class)->findKurseByTeam($team);

        return $this->render('team/academy.html.twig', [
            'title' => 'Akademie verwalten',
            'data' => $team->getAkademieUsers(),
            'kurse' => $kurse,
        ]);
    }


    /**
     * @Route("/team_custom/create", name="team_custom_create")
     */
    public function customCreate(ValidatorInterface $validator, Request $request, SecurityService $securityService, TeamService $teamService)
    {

        $team = $this->getUser()->getAdminUser();

        if ($securityService->adminCheck($this->getUser(), $team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $data1 = $teamService->create($request->get('type'), $request->get('id'), $team);

        $form = $this->createForm(NewType::class, $data1);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($data);
                $em->flush();
                return $this->redirect($this->generateUrl('team_custom') . '#' . $request->get('type'));
            }
        }

        return $this->render('team/modalView.html.twig', array('form' => $form->createView(), 'title' => $request->get('title'), 'type' => $request->get('type'), 'id' => $request->get('id')));
    }


    /**
     * @Route("/team_custom/deaktivieren", name="team_custom_deativate")
     */
    public function customDeactivate(Request $request, SecurityService $securityService, TeamService $teamService)
    {
        $team = $this->getUser()->getAdminUser();

        if ($securityService->adminCheck($this->getUser(), $team) === false) {
            return $this->redirectToRoute('team_custom');
        }

        $data = $teamService->delete($request->get('type'), $request->get('id'));

        if ($data->getTeam() == $this->getUser()->getTeam()) {
            $data->setActiv(false);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($data);
        $em->flush();
        return $this->redirectToRoute('team_custom');
    }


    /**
     * @Route("/team_abteilungen", name="team_abteilungen")
     */
    public function abteilungenAdd(ValidatorInterface $validator, Request $request, SecurityService $securityService)
    {
        $team = $this->getUser()->getAdminUser();

        if ($securityService->adminCheck($this->getUser(), $team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $abteilungen = $this->getDoctrine()->getRepository(AuditTomAbteilung::class)->findAllByTeam($team);

        if ($request->get('id')) {
            $abteilung = $this->getDoctrine()->getRepository(AuditTomAbteilung::class)->find($request->get('id'));

        } else {
            $abteilung = new AuditTomAbteilung();
            $abteilung->setActiv(true);
            $abteilung->setTeam($team);
        }

        $form = $this->createForm(AbteilungType::class, $abteilung);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $errors = $validator->validate($data);
            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($data);
                $em->flush();
                return $this->redirectToRoute('team_abteilungen');
            }
        }
        return $this->render('team/abteilungen.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'Abteilungen',
            'data' => $abteilungen,
        ]);
    }

    /**
     * @Route("/team_abteilungen/deaktivieren", name="team_abteilungen_deativate")
     */
    public function abteilungenRemove(Request $request, SecurityService $securityService)
    {
        $team = $this->getUser()->getAdminUser();

        if ($securityService->adminCheck($this->getUser(), $team) === false) {
            return $this->redirectToRoute('team_abteilungen');
        }

        $abteilung = $this->getDoctrine()->getRepository(AuditTomAbteilung::class)->findOneBy(array('id' => $request->get('id')));
        if ($abteilung->getTeam() == $this->getUser()->getTeam()) {
            $abteilung->setActiv(false);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($abteilung);
        $em->flush();
        return $this->redirectToRoute('team_abteilungen');
    }

    /**
     * @Route("/team_mitglieder", name="team_mitglieder")
     */
    public function mitgliederAdd(ValidatorInterface $validator, Request $request, InviteService $inviteService, SecurityService $securityService)
    {

        $team = $this->getUser()->getAdminUser();

        if ($securityService->adminCheck($this->getUser(), $team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $newMember = array();
        $form = $this->createForm(NewMemberType::class, $newMember);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {


            $newMembers = $form->getData();
            $lines = explode("\n", $newMembers['member']);

            if (!empty($lines)) {
                $em = $this->getDoctrine()->getManager();
                foreach ($lines as $line) {
                    $newMember = trim($line);
                    $user = $inviteService->newUser($newMember);
                    if ($user->getTeam() === null) {
                        $user->setTeam($team);
                        $em->persist($user);
                    }
                }
                $em->flush();
                return $this->redirectToRoute('team_mitglieder');
            }


        }
        return $this->render('team/member.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'Benutzer verwalten',
            'data' => $team,
        ]);
    }

    /**
     * @Route("/team_mitglieder/create", name="team_mitglieder_create")
     */
    public function teamMemberCreate(Request $request, SecurityService $securityService, TeamService $teamService, InviteService $inviteService)
    {

        $team = $this->getUser()->getAdminUser();

        if ($securityService->adminCheck($this->getUser(), $team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $newMember = array();
        $form = $this->createForm(NewMemberType::class, $newMember);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {

            $newMembers = $form->getData();
            $lines = explode("\n", $newMembers['member']);

            if (!empty($lines)) {
                $em = $this->getDoctrine()->getManager();
                foreach ($lines as $line) {
                    $newMember = trim($line);
                    $user = $inviteService->newUser($newMember);

                    switch ($request->get('type')) {
                        case 'odc':
                            if ($user->getTeam() === null) {
                                $user->setTeam($team);
                                $em->persist($user);
                                $target = $this->generateUrl('team_mitglieder');
                                break;
                            }
                        case 'academy':
                            if ($user->getAdminUser() === null) {
                                $user->setAkademieUser($team);
                                $em->persist($user);
                                $target = $this->generateUrl('akademie_admin') . '#user';
                                break;
                            }
                        default:
                            $target = $this->generateUrl('team_mitglieder');
                            break;
                    }
                }
            }
            $em->flush();
            return $this->redirect($target);
        }

        return $this->render('team/modalViewUser.html.twig', array('form' => $form->createView(), 'title' => $request->get('title'), 'type' => $request->get('type')));
    }

    /**
     * @Route("/team_mitglieder/remove", name="team_mitglieder_remove")
     */
    public function mitgliederRemove(Request $request, SecurityService $securityService)
    {
        $team = $this->getUser()->getAdminUser();

        if ($securityService->adminCheck($this->getUser(), $team) === false) {
            return $this->redirectToRoute('team_mitglieder');
        }

        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(array('id' => $request->get('id')));


        switch ($request->get('type')) {
            case 'academy' :
                $user->setAkademieUser(null);
                $target = $this->generateUrl('akademie_admin') . '#user';
                break;
            case 'odc':
                if ($this->getUser() !== $user && $user->getTeam() === $this->getUser()->getTeam()) {
                    $user->setTeam(null);
                    $user->setAdminUser(null);
                    $target = $this->generateUrl('team_mitglieder');
                }
                break;
            default:
                $target = $this->generateUrl('team_mitglieder');
                break;
        }


        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        return $this->redirect($target);
    }

    /**
     * @Route("/team_mitglieder/admin", name="team_mitglieder_admin")
     */
    public function adminToggle(Request $request, SecurityService $securityService)
    {
        $team = $this->getUser()->getAdminUser();

        if ($securityService->adminCheck($this->getUser(), $team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(array('id' => $request->get('id')));

        // Only other users and admins of the same Team can promote users
        if ($this->getUser() !== $user && $user->getTeam() === $team) {
            if ($user->getAdminUser() === null) {
                $user->setAdminUser($team);
            } else {
                $user->setAdminUser(null);
            }
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        return $this->redirectToRoute('team_mitglieder');
    }

    /**
     * @Route("/ext_team_dsb", name="team_dsb")
     */
    public function dsbAdd(Request $request, InviteService $inviteService, SecurityService $securityService)
    {
        $team = $this->getUser()->getAdminUser();

        if (!$securityService->adminCheck($this->getUser(), $team)) {
            return $this->redirectToRoute('dashboard');
        }

        $form = $this->createForm(DsbType::class);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {

            $dsb = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $email = $dsb['dsb'];
            $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(array('email' => $email));
            if (!$user) {
                $user = $inviteService->newUser($email, $team);
            }
            if (!$team->getDsbUser()) {
                $team->setDsbUser($user);
                $em->persist($team);
            }

            $em->flush();
            return $this->redirectToRoute('team_dsb', ['snack' => 'DSB wurde hinzugefügt']);
        }
        return $this->render('team/dsb.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'Externen DSB verwalten',
            'data' => $team->getDsbUser(),
            'snack' => $request->get('snack')
        ]);
    }

    /**
     * @Route("/team_dsb/remove", name="team_dsb_remove")
     */
    public function dsbRemove(Request $request, SecurityService $securityService)
    {
        $team = $this->getUser()->getAdminUser();

        if ($securityService->adminCheck($this->getUser(), $team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(array('id' => $request->get('id')));

        if ($team->getDsbUser() === $user) {
            $snack = 'Sie können sich nicht selbst aus dem Team entfernen und wurden daher nur als externer DSB entfernt.';
            if ($this->getUser() !== $team->getDsbUser()) {
                $user->setTeam(null);
                $user->setAdminUser(null);
                $user->setAkademieUser(null);
                $snack = 'Sie haben den externen DSB aus Ihrem Team entfernt';
            }
            $team->setDsbUser(null);
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($team);
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('team_dsb', ['snack' => $snack]);
    }
}
