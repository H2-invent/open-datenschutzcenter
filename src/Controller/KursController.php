<?php

namespace App\Controller;

use App\Entity\AkademieBuchungen;
use App\Entity\AkademieKurse;
use App\Form\Type\KursAnmeldungType;
use App\Form\Type\KursType;
use App\Service\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class KursController extends AbstractController
{
    /**
     * @Route("/kurse", name="kurse")
     */
    public function index()
    {
        $team = $this->getUser()->getAdminUser();
        if ($team === null) {
            return $this->redirectToRoute('dashboard');
        }

        $daten = $this->getDoctrine()->getRepository(AkademieKurse::class)->findKurseByTeam($this->getUser()->getTeam());
        return $this->render('kurs/index.html.twig', [
            'table' => $daten,
            'titel' => 'Alle erstellen Kurse',
        ]);
    }

    /**
     * @Route("/kurs/new", name="akademie_kurs_new")
     */
    public function addKurs(ValidatorInterface $validator, Request $request)
    {
        $team = $this->getUser()->getAdminUser();
        if ($team === null) {
            return $this->redirectToRoute('dashboard');
        }

        $today = new \DateTime();
        $daten = new AkademieKurse();
        $daten->addTeam($team);
        $daten->setCreatedAt($today);
        $daten->setActiv(true);

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
                return $this->redirectToRoute('kurs_anmelden',['id'=>$daten->getId()]);
            }
        }
        return $this->render('akademie/new.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'Verarbeitung erstellen',
            'daten' => $daten,
            'activNummer' => true,
            'activ' => $daten->getActiv()
        ]);
    }

    /**
     * @Route("/kurs/anmelden", name="kurs_anmelden")
     */
    public function kursAnmelden(Request $request, NotificationService $notificationService)
    {
        $team = $this->getUser()->getAdminUser();
        if ($team === null) {
            return $this->redirectToRoute('dashboard');
        }

        $kurs = $this->getDoctrine()->getRepository(AkademieKurse::class)->find($request->get('id'));

        $daten = array();
        $today = new \DateTime();
        $daten['zugewiesen'] = $today;
        $form = $this->createForm(KursAnmeldungType::class, $daten, ['user' => $team->getAkademieUsers()]);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $daten = $form->getData();

            $buchung = new AkademieBuchungen();
            $buchung->setKurs($kurs);
            $buchung->setAbgeschlossen(false);
            $buchung->setVorlage($daten['wiedervorlage']);
            $buchung->setZugewiesen($daten['zugewiesen']);
            $buchung->setInvitation(false);

            foreach ($daten['user'] as $user) {
                $buchung->setUser($user);
                if ($daten['invite'] === true) {
                    $content = $this->renderView('email/neuerKurs.html.twig',['buchung'=>$buchung]);
                    $buchung->setInvitation(true);
                    $notificationService->sendNotificationAkademie($buchung, $content);
                }
                $em->persist($buchung);
            }
            $em->flush();
            return $this->redirectToRoute('kurse');
        }
        return $this->render('akademie/new.html.twig', [
            'form' => $form->createView(),
            'title' => 'Mitglieder diesem Kurs zuweisen',
        ]);
    }

    /**
     * @Route("/kurs/deaktivieren", name="kurs_deaktivieren")
     */
    public function kursDeaktivieren(Request $request)
    {
        $team = $this->getUser()->getAdminUser();
        if ($team === null) {
            return $this->redirectToRoute('dashboard');
        }

        $kurs = $this->getDoctrine()->getRepository(AkademieKurse::class)->find($request->get('id'));
        $buchungen = $this->getDoctrine()->getRepository(AkademieBuchungen::class)->findBuchungenByTeam($team, $kurs);

        $em = $this->getDoctrine()->getManager();
        if (in_array($team, $kurs->getTeam()->toarray())) {
            $kurs->removeTeam($team);
            foreach ($buchungen as $buchung) {
                $em->remove($buchung);
            }
            $em->flush();
        }

        return $this->redirectToRoute('kurse');
    }
}
