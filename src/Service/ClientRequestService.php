<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 06.06.2020
 * Time: 19:01
 */

namespace App\Service;


use App\Entity\ClientComment;
use App\Entity\ClientRequest;
use App\Form\Type\ClientRequestType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;


class ClientRequestService
{
    private $em;
    private $formBuilder;
    private $router;
    private $notificationService;
    private $twig;

    public function __construct(EntityManagerInterface $entityManager, FormFactoryInterface $formBuilder, RouterInterface $router, NotificationService $notificationService, Environment $engine)
    {
        $this->em = $entityManager;
        $this->formBuilder = $formBuilder;
        $this->router = $router;
        $this->notificationService = $notificationService;
        $this->twig = $engine;
    }

    function userValid(ClientRequest $clientRequest, $user)
    {
        try {
            if ($clientRequest) {
                if ($clientRequest->getValiduser()) {
                    $clientRequest->setValidUser(false);
                    $clientRequest->setUserValidBy(null);
                    $comment = 'Die Nutzervalidierung wurde wieder entfernt.';
                } else {
                    $clientRequest->setValidUser(true);
                    $clientRequest->setUserValidBy($user);
                    $comment = 'Der Nutzer wurde als validiert markiert.';
                }

                $this->newComment($clientRequest, $comment, $user->getTeam()->getName() . ' > ' . $user->getUsername(), 1);

                $this->em->persist($clientRequest);
                $this->em->flush();

                return true;
            }
        } catch
        (\Exception $exception) {
            return false;
        }
    }

    function newComment(ClientRequest $clientRequest, $comment, $user, $type)
    {
        try {

            $clientComment = new ClientComment();
            $clientComment->setName($user);
            $clientComment->setInternal($type);

            $clientComment->setComment($comment);
            $clientComment->setClientRequest($clientRequest);
            $clientComment->setCreatedAt(new \DateTime());

            if ($type) {
                $content = $this->twig->render('email/client/notificationComment.html.twig', ['data' => $clientRequest, 'title' => $clientRequest->getTitle(), 'team' => $clientRequest->getTeam()]);
                $this->notificationService->sendNotificationRequest($content, $clientRequest->getEmail());
            } else {
                foreach ($clientRequest->getTeam()->getAdmins() as $admins) {
                    $content = $this->twig->render('email/client/notificationCommentInternal.html.twig', ['data' => $clientRequest, 'title' => $clientRequest->getTitle(), 'team' => $clientRequest->getTeam()]);
                    $this->notificationService->sendNotificationRequest($content, $admins->getEmail());
                }
            }


            $this->em->persist($clientComment);
            $this->em->flush();

            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    function closeRequest(ClientRequest $clientRequest, $user)
    {
        try {
            if ($clientRequest) {
                if ($clientRequest->getActiv()) {
                    $clientRequest->setActiv(false);
                    $comment = 'Diese Anfrage wurde geschlossen.';
                } else {
                    $clientRequest->setActiv(true);
                    $comment = 'Diese Anfrage wurde wieder geöffnet.';
                }

                $this->newComment($clientRequest, $comment, $user->getTeam()->getName() . ' > ' . $user->getUsername(), 1);

                $this->em->persist($clientRequest);
                $this->em->flush();

                return true;
            }
        } catch
        (\Exception $exception) {
            return false;
        }
    }

    function newRequest($team)
    {
        try {
            $clientRequest = new ClientRequest();
            $clientRequest->setUuid(uniqid());
            $clientRequest->setCreatedAt(new \DateTime());
            $clientRequest->setEmailValid(false);
            $clientRequest->setToken(md5(uniqid()));
            $clientRequest->setTeam($team);
            $clientRequest->setActiv(true);
            $clientRequest->setValidUser(false);

            $form = $this->formBuilder->create(ClientRequestType::class, $clientRequest);
            return $form;
        } catch
        (\Exception $exception) {
            return false;
        }
    }
}
