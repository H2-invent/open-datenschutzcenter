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
use Symfony\Component\Templating\EngineInterface;


class AkademieService
{
    private $em;
    private $mailer;
    private $twig;

    public function __construct(EngineInterface $engine, EntityManagerInterface $entityManager, MailerService $mailerService)
    {
        $this->em = $entityManager;
        $this->mailer = $mailerService;
        $this->twig = $engine;
    }


    function sendNotificationAkademie(AkademieBuchungen $buchung)
    {
        $this->mailer->sendEmail(
            'Datenschutzcenter',
            'info@h2-invent.com',
            $buchung->getUser()->getEmail(),
            'Ihnen wurde ein neuer Kurs zugewiesen',
            $this->twig->render('email/akademie.html.twig',[
                'user' => $buchung->getUser(),
                'buchung' => $buchung
            ])

        );

        return true;
    }
}
