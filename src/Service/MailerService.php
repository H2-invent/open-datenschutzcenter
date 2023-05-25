<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01
 */

namespace App\Service;


use nicoSWD\GPG\GPG;
use nicoSWD\GPG\PublicKey;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class MailerService
{

    private $parameter;

    public function __construct(ParameterBagInterface $parameterBag, private MailerInterface $mailer)
    {
        $this->parameter = $parameterBag;
    }

    public function sendEmail($sender, $from, $to, $betreff, $content, $attachment = array())
    {
        $this->sendViaSwiftMailer($sender, $from, $to, $betreff, $content, $attachment);
    }

    public function sendEncrypt($pgp, $sender, $from, $to, $betreff, $content, $attachment = array())
    {

        $message = (new Email())
            ->subject($betreff)
            ->from(new Address($from, $sender))
            ->to($to);

        foreach ($attachment as $data) {
            $message->attach($data['body'], $data['filename'], $data['type']);
        }

        $gpg = new GPG();
        $privat = new PublicKey($pgp);
        $data = $gpg->encrypt($privat, $content);
        $message->text($data);

        $this->mailer->send($message);
    }

    private function sendViaSwiftMailer($sender, $from, $to, $betreff, $content, $attachment = array())
    {
        $message = (new Email())
            ->subject($betreff)
            ->from(new Address($from, $sender))
            ->to($to)
            ->html(
                $content
            );
        foreach ($attachment as $data) {
            $message->attach($data['body'], $data['filename'], $data['type']);
        }
        $this->mailer->send($message);
    }
}
