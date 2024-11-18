<?php

namespace App\Controller;

use App\Entity\AkademieBuchungen;
use App\Service\NotificationService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CronController extends BaseController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    )
    {
    }

    #[Route(path: '/cron/akademie_update', name: 'cron_akademie')]
    public function updateCronAkademie(NotificationService $notificationService, Request $request, LoggerInterface $logger)
    {
        $today = new DateTime();

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

        $buchungen = $this->entityManager->getRepository(AkademieBuchungen::class)->findOffenByDate($today);


        $countNeu = 0;
        $countWdh = 0;

        // TODO: How should users with multiple teams be handled?
        foreach ($buchungen as $buchung) {
            if (!$buchung->getInvitation()) {
                $content = $this->renderView('email/neuerKurs.html.twig', ['buchung' => $buchung, 'team' => $buchung->getUser()->getTeams()->get(0)]);
                $buchung->setInvitation(true);
                $this->entityManager->persist($buchung);
                ++$countNeu;
            } else {
                $content = $this->renderView('email/errinnerungKurs.html.twig', ['buchung' => $buchung, 'team' => $buchung->getUser()->getTeams()->get(0)]);
                ++$countWdh;
            }
            $notificationService->sendNotificationAkademie($buchung, $content);
        }
        $this->entityManager->flush();
        $message = ['error' => false, 'hinweis' => 'Email mit Benachrichtigung zu offenen Kursen in der Akademie verschickt', 'neu' => $countNeu, 'wdh' => $countWdh];

        $logger->info($message['hinweis'], $message);
        return new JsonResponse($message);
    }
}
