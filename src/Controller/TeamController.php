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
use App\Form\Type\NewMemberType;
use App\Form\Type\TeamType;
use App\Form\Type\ZielType;
use App\Service\InviteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TeamController extends AbstractController
{
    /**
     * @Route("/team_edit", name="team_edit")
     */
    public function edit(ValidatorInterface $validator, Request $request)
    {
        $team = $this->getUser()->getAdminUser();

        // Admin Route only
        if ($team === null) {
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
            'title' => 'Team bearbeiten'
        ]);
    }

    /**
     * @Route("/team_ziel", name="team_ziel")
     */
    public function addZiel(ValidatorInterface $validator, Request $request)
    {
        $team = $this->getUser()->getAdminUser();

        // Admin Route only
        if ($team === null) {
            return $this->redirectToRoute('dashboard');
        }

        $ziel = new AuditTomZiele();
        $ziel->setTeam($team);
        $ziel->setActiv(true);
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
            'data' => $this->getUser()->getTeam()->getZiele(),
            'default' => $default,
        ]);
    }

    /**
     * @Route("/team_ziel/deaktivieren", name="team_ziel_deativate")
     */
    public function addZielDeactivate(Request $request)
    {
        $team = $this->getUser()->getAdminUser();

        // Admin Route only
        if ($team === null) {
            return $this->redirectToRoute('dashboard');
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
    public function addAbteilungen(ValidatorInterface $validator, Request $request)
    {
        $team = $this->getUser()->getAdminUser();

        // Admin Route only
        if ($team === null) {
            return $this->redirectToRoute('dashboard');
        }

        $abteilung = new AuditTomAbteilung();
        $abteilung->setActiv(true);
        $abteilung->setTeam($team);
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
            'data' => $this->getUser()->getTeam()->getAbteilungen(),
        ]);
    }

    /**
     * @Route("/team_abteilungen/deaktivieren", name="team_abteilungen_deativate")
     */
    public function addAbteilungenDeactivate(Request $request)
    {
        $team = $this->getUser()->getAdminUser();

        // Admin Route only
        if ($team === null) {
            return $this->redirectToRoute('dashboard');
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
    public function addMitglieder(ValidatorInterface $validator, Request $request, InviteService $inviteService)
    {
        $team = $this->getUser()->getAdminUser();

        // Admin Route only
        if ($team === null) {
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
                    $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(array('email' => $newMember));
                    if (!$user) {
                        $user = $inviteService->newUser($newMember, $team);
                    }
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
            'data' => $this->getUser()->getTeam()->getMembers(),
        ]);
    }

    /**
     * @Route("/team_mitglieder/remove", name="team_mitglieder_remove")
     */
    public function removeMitglieder(Request $request)
    {
        $team = $this->getUser()->getAdminUser();

        // Admin Route only
        if ($team === null) {
            return $this->redirectToRoute('dashboard');
        }

        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(array('id' => $request->get('id')));

        if ($this->getUser() !== $user && $user->getTeam() === $this->getUser()->getTeam()) {
            $user->setTeam(null);
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        return $this->redirectToRoute('team_mitglieder');
    }

    /**
     * @Route("/team_mitglieder/admin", name="team_mitglieder_admin")
     */
    public function adminMitglieder(Request $request)
    {
        $team = $this->getUser()->getAdminUser();

        // Admin Route only
        if ($team === null) {
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
}
