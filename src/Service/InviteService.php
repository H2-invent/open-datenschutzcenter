<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01
 */

namespace App\Service;

use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class InviteService
{
    public function __construct(
        private EntityManagerInterface $em,
        private TranslatorInterface    $translator,
        private UrlGeneratorInterface  $router,
        private MailerService          $mailer,
        private ParameterBagInterface  $parameterBag,
        private Environment            $twig,
    )
    {
    }

    public function connectUserWithEmail(User $userfromregisterId, User $user)
    {
        if ($user !== $userfromregisterId) {
            foreach ($userfromregisterId->getTeams() as $team) {
                $user->addTeam($team);
            }
            if (!$user->getAkademieUser()) {
                $user->setAkademieUser($userfromregisterId->getAkademieUser());
            }
            foreach ($userfromregisterId->getTeamDsb() as $team) {
                $user->addTeamDsb($team);
            }
            $this->em->remove($userfromregisterId);
        }

        $user->setRegisterId(null);
        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }

    public function newUser($email): User
    {
        $user = $this->em->getRepository(User::class)->findOneBy(array('email' => $email));
        if (!$user) {
            $user = new User();
            $user->setLastName('')
                ->setFirstName('')
                ->setCreatedAt(new DateTime())
                ->setRegisterId(md5(uniqid('ksdjhfkhsdkjhjksd', true)))
                ->setUsername($email)
                ->setEmail($email)
                ->setPassword('123')
                ->setUuid('123');
            $user->setEmail($email);
        }

        $user->setRegisterId(md5(uniqid('ksdjhfkhsdkjhjksd', true)));
        $this->em->persist($user);
        $this->em->flush();
        $link = $this->router->generate('invitation_accept', array('id' => $user->getRegisterId()), UrlGeneratorInterface::ABSOLUTE_URL);
        $content = $this->twig->render('email/addUser/resetting_html.html.twig', ['link' => $link]);
        $this->mailer->sendEmail(
            $this->parameterBag->get('defaultEmail'),
            $this->parameterBag->get('defaultEmail'),
            $email,
            $this->translator->trans(id: 'subject.invite', domain: 'email'),
            $content
        );
        return $user;
    }

}
