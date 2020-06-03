<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01
 */

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;


class InviteService
{


    private $em;
    private $translator;
    private $router;
    private $userManager;
    private $userMailer;
    private $userToken;
    public function __construct(MailerInterface $mailer,TokenGeneratorInterface $tokenGenerator,  UserManagerInterface $userManager, EntityManagerInterface $entityManager, TranslatorInterface $translator, UrlGeneratorInterface $urlGenerator)
    {
        $this->translator = $translator;
        $this->em = $entityManager;
        $this->router = $urlGenerator;
        $this->userManager = $userManager;
        $this->userMailer = $mailer;
        $this->userToken = $tokenGenerator;

    }

    public function newUser($email, $team){
        $user= $this->userManager->findUserByEmail($email);
        if (!$user){
            // hier wird der neue fos user angelegt
            $user = $this->userManager->createUser();
            $user->setEmail($email);
            $user->setUsername($email);
            $user->setEnabled(true);
            $user->setConfirmationToken($this->userToken->generateToken());
            $user->setPasswordRequestedAt(new \DateTime());
            $user->setPlainPassword(md5(uniqid()));
            $user->setUsername($email);
            $this->userMailer->sendResettingEmailMessage($user);
            $this->userManager->updateUser($user);

            return $user;
        }
    }

}
