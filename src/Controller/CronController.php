<?php

namespace App\Controller;

use App\Entity\AkademieBuchungen;
use App\Service\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class CronController extends AbstractController
{
    /**
     * @Route("/cron/akademie_update/sadfkljasdlkjw234jjksnvdfjfslkjc543423er", name="cron")
     */
    public function updateAkademie(NotificationService $notificationService)
    {
        $today = new \DateTime();
        $buchungen = $this->getDoctrine()->getRepository(AkademieBuchungen::class)->findOffenByDate($today);

        $em = $this->getDoctrine()->getManager();
        foreach ( $buchungen as $buchung) {
            if (!$buchung->getInvitation()) {
                $content = $this->renderView('email/neuerKurs.html.twig',['buchung'=>$buchung]);
                $buchung->setInvitation(true);
                $em->persist($buchung);
            } else {
                $content = $this->renderView('email/errinnerungKurs.html.twig',['buchung'=>$buchung]);
            }
            $notificationService->sendNotificationAkademie($buchung, $content);
        }
        $em->flush();
        return new JsonResponse(['hinweis'=>'Email verschickt']);
    }
}
