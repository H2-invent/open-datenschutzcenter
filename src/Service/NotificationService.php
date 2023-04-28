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
use Symfony\Contracts\Translation\TranslatorInterface;


class NotificationService
{
    private $em;
    private $mailer;
    private $parameterBag;


    public function __construct(
        EntityManagerInterface      $entityManager,
        MailerService               $mailerService,
        ParameterBagInterface       $parameterBag,
        private TranslatorInterface $translator,
    )
    {
        $this->em = $entityManager;
        $this->mailer = $mailerService;
        $this->parameterBag = $parameterBag;
    }


    public function sendNotificationAkademie(AkademieBuchungen $buchung, $content): bool
    {
        $this->mailer->sendEmail(
            $this->translator->trans(id: 'notification.academy.sender', domain: 'service'),
            $this->parameterBag->get('akademieEmail'),
            $buchung->getUser()->getEmail(),
            $this->translator->trans(id: 'notification.academy.lesson.assigned', domain: 'service'),
            $content
        );

        return true;
    }

    public function sendNotificationAssign($content, User $user): bool
    {
        $this->mailer->sendEmail(
            $this->translator->trans(id: 'notification.odc.sender', domain: 'service'),
            $this->parameterBag->get('defaultEmail'),
            $user->getEmail(),
            $this->translator->trans(id: 'notification.odc.element.assign', domain: 'service'),
            $content,
        );

        return true;
    }

    public function sendNotificationRequest($content, $email): bool
    {
        $this->mailer->sendEmail(
            $this->translator->trans(id: 'notification.odc.sender', domain: 'service'),
            $this->parameterBag->get('defaultEmail'),
            $email,
            $this->translator->trans(id: 'notification.odc.new.message.available', domain: 'service'),
            $content
        );

        return true;
    }

    public function sendEncrypt($pgp, $content, $email, $betreff): bool
    {
        $this->mailer->sendEncrypt(
            $pgp,
            $this->translator->trans(id: 'notification.odc.sender', domain: 'service'),
            $this->parameterBag->get('defaultEmail'),
            $email,
            $betreff,
            $content
        );

        return true;
    }

    public function sendRequestVerify($content, $email): bool
    {
        $this->mailer->sendEmail(
            $this->translator->trans(id: 'notification.odc.sender', domain: 'service'),
            $this->parameterBag->get('defaultEmail'),
            $email,
            $this->translator->trans(id: 'notification.odc.email.verify', domain: 'service'),
            $content
        );

        return true;
    }

    public function sendRequestNew($content, $email): bool
    {
        $this->mailer->sendEmail(
            $this->translator->trans(id: 'notification.odc.sender', domain: 'service'),
            $this->parameterBag->get('defaultEmail'),
            $email,
            $this->translator->trans(id: 'notification.odc.new.clientRequest', domain: 'service'),
            $content
        );

        return true;
    }
}
