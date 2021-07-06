<?php

namespace App\Controller;

use App\Entity\AkademieKurse;
use App\Form\Type\KursAnmeldungType;
use App\Form\Type\KursType;
use App\Service\AkademieService;
use App\Service\NotificationService;
use App\Service\SecurityService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class KursController extends AbstractController
{
    /**
     * @Route("/kurs/new", name="akademie_kurs_new")
     */
    public function addKurs(ValidatorInterface $validator, Request $request, SecurityService $securityService)
    {
        $team = $this->getUser()->getAdminUser();

        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('kurse');
        }

        $today = new \DateTime();
        $daten = new AkademieKurse();
        $daten->addTeam($team);
        $daten->setCreatedAt($today);
        $daten->setActiv(true);
        $daten->setUser($this->getUser());

        $form = $this->createForm(KursType::class, $daten);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $daten = $form->getData();
            $errors = $validator->validate($daten);
            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($daten);
                $em->flush();
                return $this->redirectToRoute('kurs_anmelden', ['id' => $daten->getId()]);
            }
        }
        return $this->render('akademie/new.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'Kurs erstellen',
        ]);
    }

    /**
     * @Route("/kurs/edit", name="akademie_kurs_edit")
     */
    public function editKurs(ValidatorInterface $validator, Request $request, SecurityService $securityService)
    {
        $team = $this->getUser()->getAdminUser();
        $kurs = $this->getDoctrine()->getRepository(AkademieKurse::class)->find($request->get('id'));

        if ($securityService->teamArrayDataCheck($kurs, $team) === false) {
            return $this->redirectToRoute('kurse');
        }

        if ($this->getUser() !== $kurs->getUser()) {
            return $this->redirectToRoute('kurse');
        }

        $today = new \DateTime();;
        $kurs->setCreatedAt($today);

        $form = $this->createForm(KursType::class, $kurs);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $daten = $form->getData();
            $errors = $validator->validate($daten);
            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($daten);
                $em->flush();
                return $this->redirectToRoute('kurs_anmelden', ['id' => $daten->getId()]);
            }
        }
        return $this->render('akademie/new.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'Verarbeitung erstellen',
        ]);
    }

    /**
     * @Route("/kurs/anmelden", name="kurs_anmelden")
     */
    public function kursAnmelden(Request $request, NotificationService $notificationService, SecurityService $securityService, AkademieService $akademieService)
    {
        $team = $this->getUser()->getAdminUser();
        $kurs = $this->getDoctrine()->getRepository(AkademieKurse::class)->find($request->get('id'));

        if ($securityService->teamArrayDataCheck($kurs, $team) === false) {
            return $this->redirectToRoute('kurse');
        }

        $daten = array();
        $daten['zugewiesen'] = new \DateTime();
        $form = $this->createForm(KursAnmeldungType::class, $daten, ['user' => $team->getAkademieUsers()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $daten = $form->getData();
            $akademieService->addUser($kurs, $daten);
            return $this->redirectToRoute('akademie_admin');
        }
        return $this->render('akademie/new.html.twig', [
            'form' => $form->createView(),
            'title' => 'Mitglieder diesem Kurs zuweisen',
        ]);
    }

    /**
     * @Route("/kurs/deaktivieren", name="kurs_deaktivieren")
     */
    public function kursDeaktivieren(Request $request, SecurityService $securityService, AkademieService $akademieService)
    {
        $team = $this->getUser()->getAdminUser();
        $kurs = $this->getDoctrine()->getRepository(AkademieKurse::class)->find($request->get('id'));

        if ($securityService->teamArrayDataCheck($kurs, $team) === false) {
            return $this->redirectToRoute('akademie_kurs');
        }

        $akademieService->removeKurs($team, $kurs);

        return $this->redirectToRoute('akademie_admin');
    }
}
