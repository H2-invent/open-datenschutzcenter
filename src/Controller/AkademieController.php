<?php

namespace App\Controller;

use App\Entity\AkademieBuchungen;
use App\Repository\AkademieBuchungenRepository;
use App\Service\SecurityService;
use Doctrine\ORM\EntityManagerInterface;
use Nucleos\DompdfBundle\Wrapper\DompdfWrapper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AkademieController extends AbstractController
{
    #[Route(path: '/akademie', name: 'akademie')]
    public function index(SecurityService $securityService, AkademieBuchungenRepository $bookingRepository)
    {
        $team = $this->getUser()->getAkademieUser();

        if (!$securityService->teamCheck($team)) {
            return $this->redirectToRoute('dashboard');
        }

        $bookings = $bookingRepository->findMyBuchungenByUser($this->getUser());
        return $this->render('akademie/index.html.twig', [
            'buchungen' => $bookings,
            'currentTeam' => $team,
            'today' => new \DateTime(),
        ]);
    }

    #[Route(path: '/akademie/kurs', name: 'akademie_kurs')]
    public function akademieKurs(Request $request,
                                 AkademieBuchungenRepository $bookingRepository,
                                 EntityManagerInterface $entityManager,
                                 SecurityService $securityService)
    {
        $team = $this->getUser()->getAkademieUser();

        $today = new \DateTime();
        $buchung = $bookingRepository->findOneBy(array('id' => $request->get('kurs')));

        if (!$securityService->teamCheck($team) || $buchung->getUser() !== $this->getUser()) {
            return $this->redirectToRoute('akademie');
        }

        if ($buchung->getZugewiesen() < $today) {
            if ($buchung->getStart() == null) {
                $buchung->setStart($today);
            }
            $buchung->setFinishedID(md5(uniqid()));

            $entityManager->persist($buchung);
            $entityManager->flush();

            return $this->render('akademie/kurs.html.twig', [
                'kurs' => $buchung->getKurs(),
                'buchung' => $buchung,
            ]);
        }
        return $this->redirectToRoute('akademie');


    }

    #[Route(path: '/akademie/kurs/finish', name: 'akademie_kurs_finish')]
    public function akademieKursFinish(Request $request,
                                       AkademieBuchungenRepository $bookingRepository,
                                       EntityManagerInterface $entityManager,
                                       SecurityService $securityService)
    {
        $team = $this->getUser()->getAkademieUser();

        $today = new \DateTime();
        $buchung = $bookingRepository->findOneBy(array('finishedID' => $request->get('id')));

        if (!$securityService->teamCheck($team) || $buchung->getUser() !== $this->getUser()) {
            return $this->redirectToRoute('akademie');
        }

        $newBuchung = clone $buchung;
        $buchung->setAbgeschlossen(true);
        $buchung->setEnde($today);
        $buchung->setFinishedID(null);
        $buchung->setBuchungsID(uniqid());

        $entityManager->persist($buchung);
        if ($buchung->getVorlage()) {
            $vorlage = $today->modify($buchung->getVorlage());
            $newBuchung->setZugewiesen($vorlage);
            $newBuchung->setAbgeschlossen(false);
            $newBuchung->setFinishedID(null);
            $newBuchung->setInvitation(false);
            $newBuchung->setStart(null);
            $entityManager->persist($newBuchung);
        }
        $entityManager->flush();

        return $this->redirectToRoute('akademie');
    }

    #[Route(path: '/akademie/kurs/zertifikat', name: 'akademie_kurs_zertifikat')]
    public function akademieKursZertifikat(DompdfWrapper $wrapper,
                                           AkademieBuchungenRepository $bookingRepository,
                                           Request $request)
    {
        $buchung = $bookingRepository->find($request->get('buchung'));

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
}
