<?php

namespace App\Controller;

use App\Entity\AkademieBuchungen;
use App\Entity\User;
use App\Repository\AkademieBuchungenRepository;
use App\Service\SecurityService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Nucleos\DompdfBundle\Wrapper\DompdfWrapper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/akademie', name: 'akademie')]
class AkademieController extends BaseController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private EntityManagerInterface       $em,
    )
    {
    }

    #[Route(path: '/kurs', name: '_kurs')]
    public function academyLesson(
        Request                     $request,
        SecurityService             $securityService,
        AkademieBuchungenRepository $academyBillingRepository,
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $team = $user->getAkademieUser();
        $today = new DateTime();
        $buchung = $academyBillingRepository->find($request->get('kurs'));

        if (!$securityService->teamCheck($team) || $buchung->getUser() !== $user) {
            return $this->redirectToRoute('akademie');
        }

        if ($buchung->getZugewiesen() < $today) {
            if ($buchung->getStart() == null) {
                $buchung->setStart($today);
            }
            $buchung->setFinishedID(md5(uniqid()));

            $this->em->persist($buchung);
            $this->em->flush();

            return $this->render('akademie/kurs.html.twig', [
                'kurs' => $buchung->getKurs(),
                'buchung' => $buchung,
            ]);
        }

        return $this->redirectToRoute('akademie');
    }

    #[Route(path: '/kurs/zertifikat/{id}', name: '_kurs_zertifikat')]
    #[ParamConverter(data: 'billing', class: AkademieBuchungen::class, options: ['mapping' => ['id' => 'id']])]
    public function academyLessonCertificate(
        AkademieBuchungen $billing,
        DompdfWrapper     $wrapper,
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($billing->getUser() !== $user) {
            return $this->redirectToRoute('akademie');
        }

        //Abfrage, ob der Kurs abgeschlossen ist
        if ($billing->getParticipations()[0]->isPassed()) {
            // Retrieve the HTML generated in our twig file
            $html = $this->renderView('bericht/zertifikatAkademie.html.twig', [
                'daten' => $billing,
                'team' => $user->getAkademieUser(),
                'user' => $user,
            ]);

            //Generate PDF File for Download
            $response = $wrapper->getStreamResponse($html, $this->translator->trans(id: 'certificate', domain: 'academy'));
            $response->send();

            return new Response($this->translator->trans(id: 'pdf.generateSuccessful', domain: 'general'));
        }

        return $this->redirectToRoute('akademie');
    }

    #[Route(path: '/kurs/finish', name: '_kurs_finish')]
    public function academyLessonFinish(
        Request                     $request,
        SecurityService             $securityService,
        AkademieBuchungenRepository $academyBillingRepository,
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $team = $user->getAkademieUser();

        $today = new DateTime();
        $buchung = $academyBillingRepository->findOneBy(['finishedID' => $request->get('id')]);

        if (!$securityService->teamCheck($team) || $buchung->getUser() !== $user) {
            return $this->redirectToRoute('akademie');
        }

        $newBuchung = clone $buchung;
        $buchung->setAbgeschlossen(true);
        $buchung->setEnde($today);
        $buchung->setFinishedID(null);
        $buchung->setBuchungsID(uniqid());

        $this->em->persist($buchung);
        if ($buchung->getVorlage()) {
            $vorlage = $today->modify($buchung->getVorlage());
            $newBuchung->setZugewiesen($vorlage);
            $newBuchung->setAbgeschlossen(false);
            $newBuchung->setFinishedID(null);
            $newBuchung->setInvitation(false);
            $newBuchung->setStart(null);
            $this->em->persist($newBuchung);
        }
        $this->em->flush();

        return $this->redirectToRoute('akademie');
    }

    #[Route(path: '', name: '')]
    public function index(
        SecurityService             $securityService,
        AkademieBuchungenRepository $bookingRepository
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $team = $user->getAkademieUser();

        if (!$securityService->teamCheck($team)) {
            return $this->redirectToRoute('dashboard');
        }

        $bookings = $bookingRepository->findMyBuchungenByUser($user);
        return $this->render('akademie/index.html.twig', [
            'buchungen' => $bookings,
            'currentTeam' => $team,
            'today' => new DateTime(),
        ]);
    }
}
