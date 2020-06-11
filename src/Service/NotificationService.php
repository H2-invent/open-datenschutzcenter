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
            'Datenschutzcenter',
            $this->parameterBag->get('akademieEmail'),
            $buchung->getUser()->getEmail(),
            'Ihnen wurde ein neuer Kurs zugewiesen',
            $content
        );

        return true;
    }
}
