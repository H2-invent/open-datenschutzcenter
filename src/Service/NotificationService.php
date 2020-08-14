<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01
 */

namespace App\Service;


use App\Entity\AkademieBuchungen;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class NotificationService
{
    private $em;
    private $mailer;
    private $parameterBag;


    public function __construct(EntityManagerInterface $entityManager, MailerService $mailerService, ParameterBagInterface $parameterBag)
    {
        $this->em = $entityManager;
        $this->mailer = $mailerService;
        $this->parameterBag = $parameterBag;
    }


    function sendNotificationAkademie(AkademieBuchungen $buchung, $content)
    {
        $this->mailer->sendEmail(
            'Akademie Datenschutzcenter',
            $this->parameterBag->get('akademieEmail'),
            $buchung->getUser()->getEmail(),
            'Ihnen wurde ein neuer Kurs zugewiesen',
            $content
        );

        return true;
    }

    function sendNotificationAssign($content, User $user)
    {
        $this->mailer->sendEmail(
            'Datenschutzcenter',
            $this->parameterBag->get('defaultEmail'),
            $user->getEmail(),
            'Ihnen wurde ein Element zum Bearbeiten zugewiesen',
            $content
        );

        return true;
    }

    function sendNotificationRequest($content, $email)
    {
        $this->mailer->sendEmail(
            'Datenschutzcenter',
            $this->parameterBag->get('defaultEmail'),
            $email,
            'Es ist eine neue Nachricht für Sie vorhanden',
            $content
        );

        return true;
    }

    function sendRequestVerify($content, $email)
    {
        $this->mailer->sendEmail(
            'Datenschutzcenter',
            $this->parameterBag->get('defaultEmail'),
            $email,
            'Bestätigen Sie Ihre Email Adresse',
            $content
        );

        return true;
    }

    function sendRequestNew($content, $email)
    {
        $this->mailer->sendEmail(
            'Datenschutzcenter',
            $this->parameterBag->get('defaultEmail'),
            $email,
            'Neue Kundenanfrage in Datenschutcenter',
            $content
        );

        return true;
    }

}
