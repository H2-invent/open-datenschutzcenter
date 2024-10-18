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
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class ClientRequestService
{
    public function __construct(
        private EntityManagerInterface $em,
        private FormFactoryInterface   $formBuilder,
        private NotificationService    $notificationService,
        private Environment            $twig,
        private CurrentTeamService     $currentTeamService,
        private TranslatorInterface    $translator,
    )
    {
    }

    public function closeRequest(ClientRequest $clientRequest, $user)
    {
        try {
            if ($clientRequest) {
                if ($clientRequest->getActiv()) {
                    $clientRequest->setActiv(false);
                    $comment = $this->translator->trans(id: 'request.closed', domain: 'client_request');
                } else {
                    $clientRequest->setActiv(true);
                    $clientRequest->setOpen(true);
                    $comment = $this->translator->trans(id: 'request.reopened', domain: 'client_request');
                }

                $team = $this->currentTeamService->getCurrentTeam($user);
                $this->newComment($clientRequest, $comment, $team->getKeycloakGroup() . ' > ' . $user->getUsername(), 1);

                $this->em->persist($clientRequest);
                $this->em->flush();

                return true;
            }
        } catch
        (Exception $exception) {
            return false;
        }
    }

    public function interalRequest(ClientRequest $clientRequest): bool
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
        (Exception $exception) {
        }
        return false;
    }

    public function newComment(ClientRequest $clientRequest, $comment, $user, $type): bool
    {
        try {

            $clientComment = new ClientComment();
            $clientComment->setName($user);
            $clientComment->setInternal($type);

            $clientComment->setComment($comment);
            $clientComment->setClientRequest($clientRequest);
            $clientComment->setCreatedAt(new DateTime());

            if ($type) {
                if ($clientRequest->getPgp()) {
                    $content = $this->twig->render(
                        'email/client/notificationCommentEncrypt.html.twig',
                        [
                            'data' => $clientRequest,
                            'comment' => $clientComment,
                        ],
                    );
                    $this->notificationService->sendEncrypt(
                        $clientRequest->getPgp(),
                        $content,
                        $clientRequest->getEmail(),
                        $this->translator->trans(id: 'notification.new.message.available', domain: 'service'),
                    );
                } else {
                    $content = $this->twig->render(
                        'email/client/notificationComment.html.twig',
                        [
                            'data' => $clientRequest,
                            'title' => $clientRequest->getTitle(),
                            'team' => $clientRequest->getTeam(),
                        ],
                    );
                    $this->notificationService->sendNotificationRequest($content, $clientRequest->getEmail());
                }

            } else {
                foreach ($clientRequest->getTeam()->getAdmins() as $admins) {
                    $content = $this->twig->render(
                        'email/client/notificationCommentInternal.html.twig',
                        [
                            'data' => $clientRequest,
                            'title' => $clientRequest->getTitle(),
                            'team' => $clientRequest->getTeam(),
                        ],
                    );
                    $this->notificationService->sendNotificationRequest($content, $admins->getEmail());
                }
            }

            $this->em->persist($clientComment);
            $this->em->flush();

            return true;
        } catch (Exception) {
            return false;
        }
    }

    public function newRequest($team): bool|FormInterface
    {
        try {
            $clientRequest = new ClientRequest();
            $clientRequest->setUuid(uniqid('', true));
            $clientRequest->setOpen(true);
            $clientRequest->setCreatedAt(new DateTime());
            $clientRequest->setEmailValid(false);
            $clientRequest->setToken(uniqid(bin2hex(random_bytes(150)), true));
            $clientRequest->setTeam($team);
            $clientRequest->setActiv(true);
            $clientRequest->setValidUser(false);

            $form = $this->formBuilder->create(ClientRequestType::class, $clientRequest);
            return $form;
        } catch
        (Exception $exception) {
            return false;
        }
    }

    public function userValid(ClientRequest $clientRequest, $user)
    {
        try {
            if ($clientRequest) {
                if ($clientRequest->getValiduser()) {
                    $clientRequest->setValidUser(false);
                    $clientRequest->setUserValidBy(null);
                    $comment = $this->translator->trans(id: 'user.comment.validation.removed', domain: 'client_request');
                } else {
                    $clientRequest->setValidUser(true);
                    $clientRequest->setUserValidBy($user);
                    $comment = $this->translator->trans(id: 'user.comment.validation.success', domain: 'client_reqeust');
                }

                $team = $this->currentTeamService->getCurrentTeam($user);
                $this->newComment($clientRequest, $comment, $team->getKeycloakGroup() . ' > ' . $user->getUsername(), 1);

                $this->em->persist($clientRequest);
                $this->em->flush();

                return true;
            }
        } catch
        (Exception $exception) {
            return false;
        }
    }
}
