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
use App\Entity\User;
use App\Form\Type\AbteilungType;
use App\Form\Type\DsbType;
use App\Form\Type\NewMemberType;
use App\Form\Type\TeamType;
use App\Form\Type\ZielType;
use App\Service\InviteService;
use App\Service\SecurityService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TeamController extends AbstractController
{
    /**
     * @Route("/team_edit", name="team_edit")
     */
    public function edit(ValidatorInterface $validator, Request $request, SecurityService $securityService)
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
     * @Route("/team_ziel", name="team_ziel")
     */
    public function addZiel(ValidatorInterface $validator, Request $request, SecurityService $securityService)
    {
        $team = $this->getUser()->getAdminUser();

        if ($securityService->adminCheck($this->getUser(), $team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $ziele = $this->getDoctrine()->getRepository(AuditTomZiele::class)->findActivByTeam($team);

        if ($request->get('id')) {
            $ziel = $this->getDoctrine()->getRepository(AuditTomZiele::class)->find($request->get('id'));

        } else {
            $ziel = new AuditTomZiele();
            $ziel->setActiv(true);
            $ziel->setTeam($team);
        }
        $form = $this->createForm(ZielType::class, $ziel);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $errors = $validator->validate($data);
            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($data);
                $em->flush();
                $text = 'Überprüfe Sie Ihre Eingabe';
                return $this->redirectToRoute('team_ziel');
            }
        }
        $default = $this->getDoctrine()->getRepository(AuditTomZiele::class)->findBy(array('team' => 1));
        return $this->render('team/ziel.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'Schutzziele',
            'data' => $ziele,
            'default' => $default,
        ]);
    }

    /**
     * @Route("/team_ziel/deaktivieren", name="team_ziel_deativate")
     */
    public function zielDeactivate(Request $request, SecurityService $securityService)
    {
        $team = $this->getUser()->getAdminUser();

        if ($securityService->adminCheck($this->getUser(), $team) === false) {
            return $this->redirectToRoute('team_ziel');
        }

        $ziel = $this->getDoctrine()->getRepository(AuditTomZiele::class)->findOneBy(array('id' => $request->get('id')));
        if ($ziel->getTeam() == $this->getUser()->getTeam()) {
            $ziel->setActiv(false);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($ziel);
        $em->flush();
        return $this->redirectToRoute('team_ziel');
    }

    /**
     * @Route("/team_abteilungen", name="team_abteilungen")
     */
    public function addAbteilungen(ValidatorInterface $validator, Request $request, SecurityService $securityService)
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
    public function addAbteilungenDeactivate(Request $request, SecurityService $securityService)
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
    public function addMitglieder(ValidatorInterface $validator, Request $request, InviteService $inviteService, SecurityService $securityService)
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
            'title' => 'Mitglieder verwalten',
            'data' => $team,
        ]);
    }

    /**
     * @Route("/team_mitglieder/remove", name="team_mitglieder_remove")
     */
    public function removeMitglieder(Request $request, SecurityService $securityService)
    {
        $team = $this->getUser()->getAdminUser();

        if ($securityService->adminCheck($this->getUser(), $team) === false) {
            return $this->redirectToRoute('team_mitglieder');
        }

        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(array('id' => $request->get('id')));

        if ($this->getUser() !== $user && $user->getTeam() === $this->getUser()->getTeam()) {
            $user->setTeam(null);
            $user->setAdminUser(null);
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        return $this->redirectToRoute('team_mitglieder');
    }

    /**
     * @Route("/team_mitglieder/admin", name="team_mitglieder_admin")
     */
    public function adminMitglieder(Request $request, SecurityService $securityService)
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
    public function addDsb(Request $request, InviteService $inviteService, SecurityService $securityService)
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
    public function removeDsb(Request $request, SecurityService $securityService)
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
