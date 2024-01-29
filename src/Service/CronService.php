<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 06.06.2020
 * Time: 19:01
 */

namespace App\Service;


use App\Entity\AkademieBuchungen;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;


class CronService
{
    public function __construct(
        private EntityManagerInterface $em,
        FormFactoryInterface           $formBuilder,
        private LoggerInterface        $logger,
        private TranslatorInterface    $translator,
        private NotificationService    $notificationService,
        private Environment            $environment,
    )
    {
    }

    function check($request)
    {
        $message = false;

        if ($request->get('token') !== $this->getParameter('cronToken')) {
            $message =
                [
                    'error' => true,
                    'hinweis' => $this->translator->trans(id: 'cron.token.invalid', domain: 'service'),
                    'token' => $request->get('token'),
                    'ip' => $request->getClientIp(),
                ];
            $this->logger->error($message['hinweis'], $message);
        }

        if ($this->getParameter('cronIPAdress') !== $request->getClientIp()) {
            $message = [
                'error' => true,
                'hinweis' => $this->translator->trans(id: 'cron.ip.unauthorized', domain: 'service'),
                'ip' => $request->getClientIp(),
            ];
            $this->logger->error($message['hinweis'], $message);
        }

        return $message;
    }

    public function sendEmailsForAcademy()
    {
        $today = new \DateTime();
        $buchungen = $this->em->getRepository(AkademieBuchungen::class)->findOffenByDate($today);


        $countNeu = 0;
        $countWdh = 0;

        // TODO: How should users with multiple teams be handled?
        foreach ($buchungen as $buchung) {
            if (!$buchung->getInvitation()) {
                $content = $this->environment->render('email/neuerKurs.html.twig', ['buchung' => $buchung, 'team' => $buchung->getUser()->getTeams()->get(0)]);
                $buchung->setInvitation(true);
                $this->em->persist($buchung);
                ++$countNeu;
            } else {
                $content = $this->environment->render('email/errinnerungKurs.html.twig', ['buchung' => $buchung, 'team' => $buchung->getUser()->getTeams()->get(0)]);
                ++$countWdh;
            }
            $this->notificationService->sendNotificationAkademie($buchung, $content);
        }
        $this->em->flush();
        $message = ['error' => false, 'hinweis' => 'Email mit Benachrichtigung zu offenen Kursen in der Akademie verschickt', 'neu' => $countNeu, 'wdh' => $countWdh];
        return $message;
    }
}
