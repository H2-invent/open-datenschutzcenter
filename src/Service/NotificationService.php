<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01
 */

namespace App\Service;


use App\Entity\AkademieBuchungen;
use Doctrine\ORM\EntityManagerInterface;


class NotificationService
{
    private $em;
    private $mailer;


    public function __construct(EntityManagerInterface $entityManager, MailerService $mailerService)
    {
        $this->em = $entityManager;
        $this->mailer = $mailerService;
    }


    function sendNotificationAkademie(AkademieBuchungen $buchung, $content)
    {
        $this->mailer->sendEmail(
            'Datenschutzcenter',
            'info@h2-invent.com',
            $buchung->getUser()->getEmail(),
            'Ihnen wurde ein neuer Kurs zugewiesen',
            $content
        );

        return true;
    }
}
