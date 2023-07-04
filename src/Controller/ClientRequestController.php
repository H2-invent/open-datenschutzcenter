<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\Type\ClientRequesCommentType;
use App\Form\Type\ClientRequestInternalNoteType;
use App\Form\Type\ClientRequestInternalType;
use App\Form\Type\ClientRequestType;
use App\Form\Type\ClientRequestViewType;
use App\Repository\ClientRequestRepository;
use App\Service\ClientRequestService;
use App\Service\CurrentTeamService;
use App\Service\NotificationService;
use App\Service\SecurityService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ClientRequestController extends AbstractController
{


    public function __construct(
        private readonly TranslatorInterface $translator,
        private EntityManagerInterface       $em,
    )
    {
    }

    #[Route(path: '/client-requests', name: 'client_requests')]
    public function allClientRequests(
        SecurityService         $securityService,
        CurrentTeamService      $currentTeamService,
        ClientRequestRepository $clientRequestRepository,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        $client = $clientRequestRepository->findBy(['team' => $team, 'emailValid' => true]);

        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('client_request/indexInternal.html.twig', [
            'client' => $client,
            'currentTeam' => $team
        ]);
    }

    #[Route(path: '/client/{slug}/comment', name: 'client_comment')]
    #[ParamConverter('team', options: ['mapping' => ['slug' => 'slug']])]
    public function clientComment(
        Request                 $request,
        Team                    $team,
        ClientRequestService    $clientRequestService,
        ClientRequestRepository $clientRequestRepository,
    ): Response
    {
        $data = $request->get('client_request_comment');
        $clientRequest = $clientRequestRepository->findOneBy(['token' => $request->get('token')]);

        $content = $data['comment'];
        $clientRequestService->newComment($clientRequest, $content, $clientRequest->getName(), 0);

        $snack = $this->translator->trans(id: 'save.comment', domain: 'general');
        return $this->redirectToRoute('client_show', ['slug' => $team->getSlug(), 'token' => $clientRequest->getToken(), 'snack' => $snack]);
    }

    #[Route(path: '/client-requests/comment', name: 'client_request_comment')]
    public function clientRequestComment(
        SecurityService         $securityService,
        Request                 $request,
        ClientRequestService    $clientRequestService,
        CurrentTeamService      $currentTeamService,
        ClientRequestRepository $clientRequestRepository,
    ): Response
    {
        $data = $request->get('client_request_comment');
        $clientRequest = $clientRequestRepository->find($request->get('clientRequest'));

        $team = $currentTeamService->getCurrentTeam($this->getUser());
        if ($securityService->teamDataCheck($clientRequest, $team) === false) {
            return $this->redirectToRoute('client_requests');
        }

        $content = $data['comment'];
        $clientRequestService->newComment($clientRequest, $content, $team->getKeycloakGroup() . ' > ' . $this->getUser()->getUsername(), 1);
        return $this->redirectToRoute('client_requests_show', ['id' => $clientRequest->getId()]);
    }

    #[Route(path: '/client-requests/close', name: 'client_request_close')]
    public function closeRequest(
        SecurityService         $securityService,
        Request                 $request,
        ClientRequestService    $clientRequestService,
        CurrentTeamService      $currentTeamService,
        ClientRequestRepository $clientRequestRepository,
    ): Response
    {
        $clientRequest = $clientRequestRepository->find($request->get('id'));
        $user = $this->getUser();
        $team = $currentTeamService->getCurrentTeam($user);

        if ($securityService->teamDataCheck($clientRequest, $team) && $securityService->adminCheck($user, $team)) {
            $clientRequestService->closeRequest($clientRequest, $this->getUser());

            return $this->redirectToRoute('client_requests_show', ['id' => $clientRequest->getId()]);
        }

        // if security check fails
        return $this->redirectToRoute('client_requests');
    }

    #[Route(path: '/client-requests/edit', name: 'client_requests_edit')]
    public function editClientRequests(
        SecurityService         $securityService,
        Request                 $request,
        ValidatorInterface      $validator,
        ClientRequestService    $clientRequestService,
        CurrentTeamService      $currentTeamService,
        ClientRequestRepository $clientRequestRepository,
    )
    {
        $user = $this->getUser();
        $team = $currentTeamService->getCurrentTeam($user);
        $clientRequest = $clientRequestRepository->find($request->get('id'));

        if ($securityService->teamDataCheck($clientRequest, $team) && $securityService->adminCheck($user, $team)) {
            $contentArray = [
                'Anfrage wurde geändert || Alte Nachricht: ' . $clientRequest->getTitle(),
                $this->translator->trans(id: 'email', domain: 'general') . ': ' . $clientRequest->getEmail(),
                $this->translator->trans(id: 'name', domain: 'general') . ': ' . $clientRequest->getName(),
                $this->translator->trans(id: 'reason', domain: 'general') . ': ' . $clientRequest->getItemString(),
                $this->translator->trans(id: 'description', domain: 'general') . ': ' . $clientRequest->getDescription(),
                $this->translator->trans(id: 'additionalInformation', domain: 'general') . ': ' . $clientRequest->getFirstname() . ' ' . $clientRequest->getLastname(),
                $this->translator->trans(id: 'birthday', domain: 'general') . ': ' . $clientRequest->getBirthday()->format('d.m.Y'),
                $this->translator->trans(id: 'address', domain: 'general') . ': ' . $clientRequest->getStreet() . ' ' . $clientRequest->getCity(),
            ];
            $content = implode(' | ', $contentArray);

            $form = $this->createForm(ClientRequestType::class, $clientRequest);
            $form->remove('password');
            $form->handleRequest($request);
            $errors = array();
            if ($form->isSubmitted() && $form->isValid()) {
                $clientRequest = $form->getData();
                $errors = $validator->validate($clientRequest);
                if (count($errors) == 0) {
                    $this->em->persist($clientRequest);
                    $this->em->flush();

                    $clientRequestService->newComment($clientRequest, $content, $team->getKeycloakGroup() . ' > ' . $this->getUser()->getUsername(), 1);

                    $snack = $this->translator->trans(id: 'save.changeSuccessful', domain: 'general');
                    return $this->redirectToRoute('client_requests_show', ['id' => $clientRequest->getId(), 'snack' => $snack]);
                }
            }
            return $this->render('client_request/internalEdit.html.twig', [
                'data' => $clientRequest,
                'team' => $team,
                'form' => $form->createView()
            ]);
        }

        // if security check fails
        return $this->redirectToRoute('client_requests');
    }

    #[Route(path: '/client/{slug}', name: 'client_index')]
    #[ParamConverter('team', options: ['mapping' => ['slug' => 'slug']])]
    public function index(
        Team                    $team,
        Request                 $request,
        ClientRequestRepository $clientRequestRepository,
    ): Response
    {
        $form = $this->createForm(ClientRequestViewType::class);
        $form->handleRequest($request);
        $snack = $request->get('snack');
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $search = $form->getData();
            $pass = sha1($search['password']);
            $clientRequest = $clientRequestRepository->findOneBy([
                'uuid' => $search['uuid'],
                'email' => $search['email'],
                'password' => $pass,
                'open' => true
            ]);

            if (count($errors) == 0 && $clientRequest) {
                return $this->redirectToRoute('client_show', ['slug' => $team->getSlug(), 'token' => $clientRequest->getToken()]);
            }
            $snack = $this->translator->trans(id: 'login.error.retry', domain: 'client_request');
        }

        return $this->render('client_request/index.html.twig', [
            'form' => $form->createView(),
            'team' => $team,
            'snack' => $snack
        ]);
    }

    #[Route(path: '/client-requests/internalNote', name: 'client_requests_internal_note')]
    public function internalNoteClientRequests(
        SecurityService         $securityService,
        Request                 $request,
        ValidatorInterface      $validator,
        CurrentTeamService      $currentTeamService,
        ClientRequestRepository $clientRequestRepository,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        $clientRequest = $clientRequestRepository->find($request->get('id'));

        if ($securityService->teamDataCheck($clientRequest, $team) === false) {
            return $this->redirectToRoute('client_requests');
        }
        $form = $this->createForm(ClientRequestInternalNoteType::class, $clientRequest);
        $form->handleRequest($request);
        $errors = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $clientRequest = $form->getData();

            $errors = $validator->validate($clientRequest);
            if (count($errors) == 0) {
                $this->em->persist($clientRequest);
                $this->em->flush();

                return $this->redirectToRoute(
                    'client_requests_show',
                    [
                        'id' => $clientRequest->getId(),
                        'snack' => $this->translator->trans(id: 'save.changesSuccessful', domain: 'general'),
                    ],
                );
            }
        }

        return $this->render(
            'client_request/internalEdit.html.twig',
            [
                'data' => $clientRequest,
                'team' => $team,
                'form' => $form->createView(),
            ],
        );
    }

    #[Route(path: '/client-requests/internal', name: 'client_request_make_internal')]
    public function makeInternalRequest(
        SecurityService         $securityService,
        Request                 $request,
        ClientRequestService    $clientRequestService,
        CurrentTeamService      $currentTeamService,
        ClientRequestRepository $clientRequestRepository,
    ): Response
    {
        $clientRequest = $clientRequestRepository->find($request->get('id'));
        $user = $this->getUser();
        $team = $currentTeamService->getCurrentTeam($user);

        if ($securityService->teamDataCheck($clientRequest, $team) && $securityService->adminCheck($user, $team)) {
            if ($clientRequestService->interalRequest($clientRequest)) {
                $snack = $this->translator->trans(id: 'save.changesSuccessful', domain: 'general');
            } else {
                $snack = $this->translator->trans(id: 'error.retry', domain: 'general');
            }
            return $this->redirectToRoute('client_requests_show', ['id' => $clientRequest->getId(), 'snack' => $snack]);
        }

        // if security check fails
        return $this->redirectToRoute('client_requests');
    }

    #[Route(path: '/client/{slug}/new', name: 'client_new')]
    #[ParamConverter('team', options: ['mapping' => ['slug' => 'slug']])]
    public function newRequest(
        Request              $request,
        ValidatorInterface   $validator,
        Team                 $team,
        ClientRequestService $clientRequestService,
        NotificationService  $notificationService,
    ): Response
    {
        $form = $clientRequestService->newRequest($team);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $clientRequest = $form->getData();
            $clientRequest->setPassword(sha1($clientRequest->getPassword()));

            $errors = $validator->validate($clientRequest);
            if (count($errors) == 0) {
                $this->em->persist($clientRequest);

                if ($clientRequest->getPgp()) {
                    $content = $this->renderView('email/client/notificationVerifyEncrypt.html.twig', ['data' => $clientRequest]);
                    try {
                        $notificationService->sendEncrypt(
                            $clientRequest->getPgp(),
                            $content,
                            $clientRequest->getEmail(),
                            $this->translator->trans(id: 'emailFromOdc', domain: 'client_request'));
                    } catch (Exception $e) {
                        $clientRequest->setPgp(null);
                        $this->em->persist($clientRequest);
                    }
                }
                if (!$clientRequest->getPgp()) {
                    $content = $this->renderView(
                        'email/client/notificationVerify.html.twig',
                        [
                            'data' => $clientRequest,
                            'title' => $clientRequest->getTitle(),
                            'team' => $clientRequest->getTeam()
                        ]
                    );
                    $notificationService->sendRequestVerify($content, $clientRequest->getEmail());
                }
                $this->em->flush();
                return $this->redirectToRoute('client_show', ['slug' => $team->getSlug(), 'token' => $clientRequest->getToken()]);
            }
        }
        return $this->render('client_request/new.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'team' => $team
        ]);
    }

    #[Route(path: '/client-requests/show', name: 'client_requests_show')]
    public function showClientRequests(
        SecurityService         $securityService,
        Request                 $request,
        CurrentTeamService      $currentTeamService,
        ClientRequestRepository $clientRequestRepository,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        $clientRequest = $clientRequestRepository->find($request->get('id'));

        if ($securityService->teamDataCheck($clientRequest, $team) === false) {
            return $this->redirectToRoute('client_requests');
        }

        $form = $this->createForm(ClientRequesCommentType::class);

        return $this->render('client_request/internalShow.html.twig', [
            'data' => $clientRequest,
            'team' => $team,
            'form' => $form->createView(),
            'snack' => $request->get('snack')
        ]);
    }

    #[Route(path: '/client/{slug}/show', name: 'client_show')]
    #[ParamConverter('team', options: ['mapping' => ['slug' => 'slug']])]
    public function showRequest(
        Request                 $request,
        Team                    $team,
        ClientRequestRepository $clientRequestRepository
    ): Response
    {
        $clientRequest = $clientRequestRepository->findOneBy(['token' => $request->get('token'), 'open' => true]);

        if ($clientRequest) {
            $form = $this->createForm(ClientRequesCommentType::class);
            return $this->render('client_request/show.html.twig', [
                'data' => $clientRequest,
                'team' => $team,
                'form' => $form->createView(),
            ]);
        }
        $snack = $this->translator->trans(id: 'noTicketMatched', domain: 'client_request');
        return $this->redirectToRoute('client_index', ['slug' => $team->getSlug(), 'snack' => $snack]);
    }

    #[Route(path: '/client/{slug}/verify', name: 'client_valid')]
    #[ParamConverter('team', options: ['mapping' => ['slug' => 'slug']])]
    public function validateRequest(
        Request                 $request,
        Team                    $team,
        NotificationService     $notificationService,
        ClientRequestService    $clientRequestService,
        ClientRequestRepository $clientRequestRepository,
    ): Response
    {
        $clientRequest = $clientRequestRepository->findOneBy(['uuid' => $request->get('token')]);
        $snack = $this->translator->trans(id: 'noOpenTicket', domain: 'client_request');
        if ($clientRequest) {
            if (!$clientRequest->getEmailValid()) {
                $clientRequest->setEmailValid(true);
                $this->em->persist($clientRequest);
                $this->em->flush();

                $contentInternal = $this->renderView('email/client/notificationNew.html.twig', ['data' => $clientRequest, 'title' => $clientRequest->getTitle(), 'team' => $clientRequest->getTeam()]);
                foreach ($clientRequest->getTeam()->getAdmins() as $admin) {
                    $notificationService->sendRequestNew($contentInternal, $admin->getEmail());
                }
                $content = $this->translator->trans(id: 'email.verify', domain: 'client_request');
                $clientRequestService->newComment($clientRequest, $content, $clientRequest->getName(), 1);
                $snack = $this->translator->trans(id: 'email.verify', domain: 'client_request');
            } else {
                $snack = $this->translator->trans(id: 'email.alreadyVerified', domain: 'client_request');
            }
        }
        return $this->redirectToRoute('client_index', ['slug' => $team->getSlug(), 'snack' => $snack]);
    }

    #[Route(path: '/client-requests/userValidate', name: 'client_valid_user')]
    public function validateUserRequest(
        SecurityService         $securityService,
        Request                 $request,
        ClientRequestService    $clientRequestService,
        CurrentTeamService      $currentTeamService,
        ClientRequestRepository $clientRequestRepository,
    ): Response
    {
        $clientRequest = $clientRequestRepository->find($request->get('id'));
        $user = $this->getUser();
        $team = $currentTeamService->getCurrentTeam($user);

        if ($securityService->teamDataCheck($clientRequest, $team) && $securityService->adminCheck($user, $team)) {
            $clientRequestService->userValid($clientRequest, $user);
            return $this->redirectToRoute('client_requests_show', ['id' => $clientRequest->getId()]);
        }

        // if security check fails
        return $this->redirectToRoute('client_requests');
    }
}
