<?php

namespace App\Controller;

use App\Entity\AkademieBuchungen;
use App\Service\NotificationService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CronController extends AbstractController
{
    /**
     * @Route("/cron/akademie_update", name="cron_akademie")
     */
    public function updateCronAkademie(NotificationService $notificationService, Request $request, LoggerInterface $logger)
    {
        $today = new \DateTime();

        if ($request->get('token') !== $this->getParameter('cronToken')) {
            $message = ['error' => false, 'hinweis' => 'Token fehlerhaft', 'token' => $request->get('token'), 'ip' => $request->getClientIp()];
            $logger->error($message['hinweis'], $message);
            return new JsonResponse($message);
        }

        if ($this->getParameter('cronIPAdress') !== $request->getClientIp()) {
            $message = ['error' => false, 'hinweis' => 'IP Adresse fuer Cron Jobs nicht zugelassen', 'ip' => $request->getClientIp()];
            $logger->error($message['hinweis'], $message);
            return new JsonResponse($message);
        }

        $buchungen = $this->getDoctrine()->getRepository(AkademieBuchungen::class)->findOffenByDate($today);

        $em = $this->getDoctrine()->getManager();
        $countNeu = 0;
        $countWdh = 0;
        foreach ($buchungen as $buchung) {
            if (!$buchung->getInvitation()) {
                $content = $this->renderView('email/neuerKurs.html.twig', ['buchung' => $buchung, 'team' => $buchung->getUser()->getTeam()]);
                $buchung->setInvitation(true);
                $em->persist($buchung);
                ++$countNeu;
            } else {
                $content = $this->renderView('email/errinnerungKurs.html.twig', ['buchung' => $buchung]);
                ++$countWdh;
            }
            $notificationService->sendNotificationAkademie($buchung, $content);
        }
        $em->flush();
        $message = ['error' => false, 'hinweis' => 'Email mit Benachrichtigung zu offenen Kursen in der Akademie verschickt', 'neu' => $countNeu, 'wdh' => $countWdh];

        $logger->info($message['hinweis'], $message);
        return new JsonResponse($message);
    }
}
