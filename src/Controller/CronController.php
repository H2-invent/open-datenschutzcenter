<?php

namespace App\Controller;

use App\Entity\AkademieBuchungen;
use App\Repository\AkademieBuchungenRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CronController extends AbstractController
{
    #[Route(path: '/cron/akademie_update', name: 'cron_akademie')]
    public function updateCronAkademie(NotificationService $notificationService,
                                       EntityManagerInterface $entityManager,
                                       AkademieBuchungenRepository $bookingRepository,
                                       Request $request,
                                       LoggerInterface $logger)
    {
        $today = new \DateTime();

        if ($request->get('token') !== $this->getParameter('cronToken')) {
            $message = ['error' => true, 'hinweis' => 'Token fehlerhaft', 'token' => $request->get('token'), 'ip' => $request->getClientIp()];
            $logger->error($message['hinweis'], $message);
            return new JsonResponse($message);
        }

        if ($this->getParameter('cronIPAdress') !== $request->getClientIp()) {
            $message = ['error' => true, 'hinweis' => 'IP Adresse fuer Cron Jobs nicht zugelassen', 'ip' => $request->getClientIp()];
            $logger->error($message['hinweis'], $message);
            return new JsonResponse($message);
        }

        $bookings = $bookingRepository->findOffenByDate($today);
        $countNeu = 0;
        $countWdh = 0;

        // TODO: How should users with multiple teams be handled?
        foreach ($bookings as $booking) {
            if (!$booking->getInvitation()) {
                $content = $this->renderView('email/neuerKurs.html.twig', ['buchung' => $booking, 'team' => $booking->getUser()->getTeams()->get(0)]);
                $booking->setInvitation(true);
                $entityManager->persist($booking);
                ++$countNeu;
            } else {
                $content = $this->renderView('email/errinnerungKurs.html.twig', ['buchung' => $booking, 'team' => $booking->getUser()->getTeams()->get(0)]);
                ++$countWdh;
            }
            $notificationService->sendNotificationAkademie($booking, $content);
        }
        $entityManager->flush();
        $message = ['error' => false, 'hinweis' => 'Email mit Benachrichtigung zu offenen Kursen in der Akademie verschickt', 'neu' => $countNeu, 'wdh' => $countWdh];

        $logger->info($message['hinweis'], $message);
        return new JsonResponse($message);
    }
}
