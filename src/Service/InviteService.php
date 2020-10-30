<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01
 */

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;


class InviteService
{


    private $em;
    private $translator;
    private $router;
    private $mailer;
    private $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag, MailerService $mailerService, EntityManagerInterface $entityManager, TranslatorInterface $translator, UrlGeneratorInterface $urlGenerator)
    {
        $this->translator = $translator;
        $this->em = $entityManager;
        $this->router = $urlGenerator;

        $this->mailer = $mailerService;

        $this->parameterBag = $parameterBag;
    }

    public function newUser($email)
    {
        $user = $this->em->getRepository(User::class)->findOneBy(array('email' => $email));
        if (!$user) {
            // hier wird der neue fos user angelegt
            $user = new User();
            $user->setLastName('')
                ->setFirstName('')
                ->setCreatedAt(new \DateTime())
                ->setRegisterId(md5(uniqid('ksdjhfkhsdkjhjksd', true)))
                ->setUsername($email)
                ->setEmail($email)
                ->setPassword('123')
                ->setUuid('123');
            $user->setEmail($email);
            $this->em->persist($user);
            $this->em->flush();
            //here we send the invitation Email for this fancy lancy user
            //todo make the email nice and smoooooooth
            $this->mailer->sendEmail(
                $this->parameterBag->get('defaultEmail'),
                $this->parameterBag->get('defaultEmail'),
                $email,
                $this->translator->trans('Einladung zum ODC'),
                $this->router->generate('invitation_accept', array('id' => $user->getRegisterId()),UrlGeneratorInterface::ABSOLUTE_URL));
        }
        return $user;
    }

    public function connectUserWithEmail(User $userfromregisterId, User $user)
    {
        if (!$user->getTeam()) {
            $user->setTeam($userfromregisterId->getTeam());
        }
        if (!$user->getAkademieUser()) {
            $user->setAkademieUser($userfromregisterId->getAkademieUser());
        }
        foreach ($user->getTeamDsb() as $data) {
            $user->addTeamDsb($data);
        }
        $this->em->persist($user);
        $this->em->remove($userfromregisterId);
        $this->em->flush();
        return $user;
    }

}
