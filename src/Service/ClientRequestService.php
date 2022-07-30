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
use Twig\Environment;


class ClientRequestService
{
    private EntityManagerInterface $em;
    private FormFactoryInterface $formBuilder;
    private NotificationService $notificationService;
    private Environment $twig;
    private CurrentTeamService $currentTeamService;

    public function __construct(EntityManagerInterface $entityManager, FormFactoryInterface $formBuilder, NotificationService $notificationService, Environment $engine, CurrentTeamService $currentTeamService)
    {
        $this->em = $entityManager;
        $this->formBuilder = $formBuilder;
        $this->notificationService = $notificationService;
        $this->twig = $engine;
        $this->currentTeamService = $currentTeamService;
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

                $team = $this->currentTeamService->getTeamFromSession($user);
                $this->newComment($clientRequest, $comment, $team->getDisplayName() . ' > ' . $user->getUsername(), 1);

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
                if ($clientRequest->getPgp()) {
                    $content = $this->twig->render('email/client/notificationCommentEncrypt.html.twig', ['data' => $clientRequest, 'comment' => $clientComment]);
                    $this->notificationService->sendEncrypt($clientRequest->getPgp(), $content, $clientRequest->getEmail(), 'Neue Nachricht vorhanden');
                } else {
                    $content = $this->twig->render('email/client/notificationComment.html.twig', ['data' => $clientRequest, 'title' => $clientRequest->getTitle(), 'team' => $clientRequest->getTeam()]);
                    $this->notificationService->sendNotificationRequest($content, $clientRequest->getEmail());
                }

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
                    $clientRequest->setOpen(true);
                    $comment = 'Diese Anfrage wurde wieder geöffnet.';
                }

                $team = $this->currentTeamService->getTeamFromSession($user);
                $this->newComment($clientRequest, $comment, $team->getDisplayName() . ' > ' . $user->getUsername(), 1);

                $this->em->persist($clientRequest);
                $this->em->flush();

                return true;
            }
        } catch
        (\Exception $exception) {
            return false;
        }
    }

    function interalRequest(ClientRequest $clientRequest)
    {
        try {
            if ($clientRequest) {
                if (!$clientRequest->getActiv()) {
                    if ($clientRequest->getOpen()) {
                        $clientRequest->setOpen(false);
                    } else {
                        $clientRequest->setOpen(true);
                    }
                }

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
            $clientRequest->setUuid(uniqid('', true));
            $clientRequest->setOpen(true);
            $clientRequest->setCreatedAt(new \DateTime());
            $clientRequest->setEmailValid(false);
            $clientRequest->setToken(uniqid(bin2hex(random_bytes(150)), true));
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
