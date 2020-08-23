<?php

namespace App\Controller;

use App\Entity\AkademieBuchungen;
use App\Entity\User;
use App\Form\Type\NewMemberType;
use App\Service\InviteService;
use App\Service\SecurityService;
use Nucleos\DompdfBundle\Wrapper\DompdfWrapper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AkademieController extends AbstractController
{
    /**
     * @Route("/akademie", name="akademie")
     */
    public function index(SecurityService $securityService)
    {
        $team = $this->getUser()->getAkademieUser();

        if (!$securityService->teamCheck($team)) {
            return $this->redirectToRoute('dashboard');
        }

        $buchungen = $this->getDoctrine()->getRepository(AkademieBuchungen::class)->findMyBuchungenByUser($this->getUser());
        return $this->render('akademie/index.html.twig', [
            'buchungen' => $buchungen,
            'today' => $today = new \DateTime(),
        ]);
    }

    /**
     * @Route("/akademie/kurs", name="akademie_kurs")
     */
    public function akademieKurs(Request $request, SecurityService $securityService)
    {
        $team = $this->getUser()->getAkademieUser();

        $today = new \DateTime();
        $buchung = $this->getDoctrine()->getRepository(AkademieBuchungen::class)->findOneBy(array('id' => $request->get('kurs')));

        if (!$securityService->teamCheck($team) || $buchung->getUser() !== $this->getUser()) {
            return $this->redirectToRoute('akademie');
        }

        if ($buchung->getZugewiesen() < $today) {
            if ($buchung->getStart() == null) {
                $buchung->setStart($today);
            }
            $buchung->setFinishedID(md5(uniqid()));

            $em = $this->getDoctrine()->getManager();
            $em->persist($buchung);
            $em->flush();

            return $this->render('akademie/kurs.html.twig', [
                'kurs' => $buchung->getKurs(),
                'buchung' => $buchung,
            ]);
        }
        return $this->redirectToRoute('akademie');


    }

    /**
     * @Route("/akademie/kurs/finish", name="akademie_kurs_finish")
     */
    public function akademieKursFinish(Request $request, SecurityService $securityService)
    {
        $team = $this->getUser()->getAkademieUser();

        $today = new \DateTime();
        $buchung = $this->getDoctrine()->getRepository(AkademieBuchungen::class)->findOneBy(array('finishedID' => $request->get('id')));

        if (!$securityService->teamCheck($team) || $buchung->getUser() !== $this->getUser()) {
            return $this->redirectToRoute('akademie');
        }

        $newBuchung = clone $buchung;
        $buchung->setAbgeschlossen(true);
        $buchung->setEnde($today);
        $buchung->setFinishedID(null);
        $buchung->setBuchungsID(uniqid());

        $em = $this->getDoctrine()->getManager();
        $em->persist($buchung);
        if ($buchung->getVorlage()) {
            $vorlage = $today->modify($buchung->getVorlage());
            $newBuchung->setZugewiesen($vorlage);
            $newBuchung->setAbgeschlossen(false);
            $newBuchung->setFinishedID(null);
            $newBuchung->setInvitation(false);
            $newBuchung->setStart(null);
            $em->persist($newBuchung);
        }
        $em->flush();

        return $this->redirectToRoute('akademie');
    }

    /**
     * @Route("/akademie/kurs/zertifikat", name="akademie_kurs_zertifikat")
     */
    public function akademieKursZertifikat(DompdfWrapper $wrapper, Request $request)
    {
        $buchung = $this->getDoctrine()->getRepository(AkademieBuchungen::class)->find($request->get('buchung'));

        if ($buchung->getUser() !== $this->getUser()) {
            return $this->redirectToRoute('akademie');
        }

        //Abfrage ob der Kurs abgeschlossen ist
        if ($buchung->getAbgeschlossen() === true) {
            // Retrieve the HTML generated in our twig file
            $html = $this->renderView('bericht/zertifikatAkademie.html.twig', [
                'daten' => $buchung,
                'team' => $this->getUser()->getAkademieUser(),
                'user' => $this->getUser(),
            ]);

            //Generate PDF File for Download
            $response = $wrapper->getStreamResponse($html, "Zertifikat.pdf");
            $response->send();
            return new Response("The PDF file has been succesfully generated !");
        }

        return $this->redirectToRoute('akademie');
    }

    /**
     * @Route("/akademie/mitglieder", name="akademie_mitglieder")
     */
    public function addMitglieder(ValidatorInterface $validator, Request $request, InviteService $inviteService, SecurityService $securityService)
    {
        $team = $this->getUser()->getAdminUser();

        // Admin Route only
        if (!$securityService->adminCheck($this->getUser(), $team)) {
            return $this->redirectToRoute('dashboard');
        }

        //Neue Mitglieder Form
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
                    if ($user->getAkademieUser() === null) {
                        $user->setAkademieUser($team);
                        $em->persist($user);

                    }
                }
                $em->flush();
                return $this->redirectToRoute('akademie_mitglieder');
            }
        }
        return $this->render('akademie/member.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'Mitglieder verwalten',
            'data' => $team->getAkademieUsers(),
        ]);
    }

    /**
     * @Route("/akademie/mitglieder/remove", name="akademie_mitglieder_remove")
     */
    public function removeMitglieder(Request $request, SecurityService $securityService)
    {
        $team = $this->getUser()->getAdminUser();

        // Admin Route only
        if (!$securityService->adminCheck($this->getUser(), $team)) {
            return $this->redirectToRoute('dashboard');
        }

        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(array('id' => $request->get('id')));

        // Nur Admin User dürfen eigene Akademie Mitglieder entfernen
        if ($user->getAkademieUser() === $team) {
            $user->setAkademieUser(null);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        return $this->redirectToRoute('akademie_mitglieder');
    }
}
