<?php

namespace App\Controller;

use App\Entity\AkademieBuchungen;
use App\Service\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CronController extends AbstractController
{
    /**
     * @Route("/cron/akademie_update", name="cron_akademie")
     */
    public function updateCronAkademie(NotificationService $notificationService, Request $request)
    {
        $today = new \DateTime();

        if ($request->get('token') !== $this->getParameter('cronToken')) {
            return new JsonResponse(['hinweis' => 'Token fehlerhaft']);
        }

        if ($this->getParameter('cronIPAdress') !== $request->getClientIp()) {
            return new JsonResponse(['hinweis' => 'IP Adresse fuer Cron Jobs nicht zugelassen']);
        }

        $buchungen = $this->getDoctrine()->getRepository(AkademieBuchungen::class)->findOffenByDate($today);

        $em = $this->getDoctrine()->getManager();
        foreach ($buchungen as $buchung) {
            if (!$buchung->getInvitation()) {
                $content = $this->renderView('email/neuerKurs.html.twig', ['buchung' => $buchung]);
                $buchung->setInvitation(true);
                $em->persist($buchung);
            } else {
                $content = $this->renderView('email/errinnerungKurs.html.twig',['buchung'=>$buchung]);
            }
            $notificationService->sendNotificationAkademie($buchung, $content);
        }
        $em->flush();
        return new JsonResponse(['hinweis' => 'Email mit Benachrichtigung verschickt']);
    }
}
