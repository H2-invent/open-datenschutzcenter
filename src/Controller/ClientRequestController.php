<?php

namespace App\Controller;

use App\Entity\ClientRequest;
use App\Entity\Team;
use App\Form\Type\ClientRequesCommentType;
use App\Form\Type\ClientRequestInternalNoteType;
use App\Form\Type\ClientRequestInternalType;
use App\Form\Type\ClientRequestType;
use App\Form\Type\ClientRequestViewType;
use App\Service\ClientRequestService;
use App\Service\NotificationService;
use App\Service\SecurityService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ClientRequestController extends AbstractController
{
    /**
     * @Route("/client-requests", name="client_requests")
     */
    public function allClientRequests(SecurityService $securityService)
    {
        $team = $this->getUser()->getTeam();
        $client = $this->getDoctrine()->getRepository(ClientRequest::class)->findBy(['team' => $team, 'emailValid' => true]);

        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('client_request/indexInternal.html.twig', [
            'client' => $client,
            'team' => $team
        ]);
    }

    /**
     * @Route("/client-requests/show", name="client_requests_show")
     */
    public function showClientRequests(SecurityService $securityService, Request $request)
    {

        $team = $this->getUser()->getTeam();
        $clientRequest = $this->getDoctrine()->getRepository(ClientRequest::class)->find($request->get('id'));

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

    /**
     * @Route("/client-requests/comment", name="client_request_comment")
     */
    public function clientRequestComment(SecurityService $securityService, Request $request, ClientRequestService $clientRequestService)
    {
        $data = $request->get('client_reques_comment');
        $clientRequest = $this->getDoctrine()->getRepository(ClientRequest::class)->find($request->get('clientRequest'));

        $team = $this->getUser()->getTeam();
        if ($securityService->teamDataCheck($clientRequest, $team) === false) {
            return $this->redirectToRoute('client_requests');
        }

        $content = $data['comment'];
        $clientRequestService->newComment($clientRequest, $content, $this->getUser()->getTeam()->getName() . ' > ' . $this->getUser()->getUsername(), 1);
        return $this->redirectToRoute('client_requests_show', ['id' => $clientRequest->getId()]);
    }

    /**
     * @Route("/client-requests/userValidate", name="client_valid_user")
     */
    public function validateUserRequest(SecurityService $securityService, Request $request, ClientRequestService $clientRequestService)
    {
        $clientRequest = $this->getDoctrine()->getRepository(ClientRequest::class)->find($request->get('id'));

        $team = $this->getUser()->getAdminUser();
        if ($securityService->teamDataCheck($clientRequest, $team) === false) {
            return $this->redirectToRoute('client_requests');
        }

        $clientRequestService->userValid($clientRequest, $this->getUser());
        return $this->redirectToRoute('client_requests_show', ['id' => $clientRequest->getId()]);
    }

    /**
     * @Route("/client-requests/close", name="client_request_close")
     */
    public function closeRequest(SecurityService $securityService, Request $request, ClientRequestService $clientRequestService)
    {
        $clientRequest = $this->getDoctrine()->getRepository(ClientRequest::class)->find($request->get('id'));

        $team = $this->getUser()->getAdminUser();
        if ($securityService->teamDataCheck($clientRequest, $team) === false) {
            return $this->redirectToRoute('client_requests');
        }
        $clientRequestService->closeRequest($clientRequest, $this->getUser());

        return $this->redirectToRoute('client_requests_show', ['id' => $clientRequest->getId()]);
    }

    /**
     * @Route("/client-requests/internalNote", name="client_requests_internal_note")
     */
    public function internalNoteClientRequests(SecurityService $securityService, Request $request, ValidatorInterface $validator)
    {

        $team = $this->getUser()->getTeam();
        $clientRequest = $this->getDoctrine()->getRepository(ClientRequest::class)->find($request->get('id'));

        if ($securityService->teamDataCheck($clientRequest, $team) === false) {
            return $this->redirectToRoute('client_requests');
        }
        $form = $this->createForm(ClientRequestInternalNoteType::class, $clientRequest);
        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $clientRequest = $form->getData();

            $errors = $validator->validate($clientRequest);
            if (count($errors) == 0) {
                $em->persist($clientRequest);
                $em->flush();

                return $this->redirectToRoute('client_requests_show', ['id' => $clientRequest->getId(), 'snack' => 'Änderung gespeichert']);
            }
        }
        return $this->render('client_request/internalEdit.html.twig', ['data' => $clientRequest, 'team' => $team, 'form' => $form->createView(),]);
    }


    /**
     * @Route("/client-requests/edit", name="client_requests_edit")
     */
    public function editClientRequests(SecurityService $securityService, Request $request, ValidatorInterface $validator, ClientRequestService $clientRequestService)
    {

        $team = $this->getUser()->getAdminUser();
        $clientRequest = $this->getDoctrine()->getRepository(ClientRequest::class)->find($request->get('id'));
        if ($securityService->teamDataCheck($clientRequest, $team) === false) {
            return $this->redirectToRoute('client_requests');
        }
        $content = 'Anfrage wurde geändert <br><br>Alte Nachricht: ' . $clientRequest->getTitle() . '<br>Grund: ' . $clientRequest->getItemString() . '<br><br>Beschreibung' . $clientRequest->getDescription();

        $form = $this->createForm(ClientRequestType::class, $clientRequest);
        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $clientRequest = $form->getData();
            $errors = $validator->validate($clientRequest);
            if (count($errors) == 0) {
                $em->persist($clientRequest);
                $em->flush();

                $clientRequestService->newComment($clientRequest, $content, $this->getUser()->getTeam()->getName() . ' > ' . $this->getUser()->getUsername(), 1);

                return $this->redirectToRoute('client_requests_show', ['id' => $clientRequest->getId(), 'snack' => 'Änderung gespeichert']);
            }
        }
        return $this->render('client_request/internalEdit.html.twig', ['data' => $clientRequest, 'team' => $team, 'form' => $form->createView()]);
    }


    /**
     * @Route("/client/{slug}", name="client_index")
     * @ParamConverter("team", options={"mapping": {"slug": "slug"}})
     */
    public function index(Team $team, Request $request)
    {
        $form = $this->createForm(ClientRequestViewType::class);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $search = $form->getData();
            $clientRequest = $this->getDoctrine()->getRepository(ClientRequest::class)->findOneBy(['uuid' => $search['uuid'], 'email' => $search['email']]);

            if (count($errors) == 0 && $clientRequest) {
                return $this->redirectToRoute('client_show', ['slug' => $team->getSlug(), 'token' => $clientRequest->getToken()]);
            }
        }

        return $this->render('client_request/index.html.twig', [
            'form' => $form->createView(),
            'team' => $team
        ]);
    }

    /**
     * @Route("/client/{slug}/new", name="client_new")
     * @ParamConverter("team", options={"mapping": {"slug": "slug"}})
     */
    public function newRequest(Request $request, ValidatorInterface $validator, Team $team, ClientRequestService $clientRequestService, NotificationService $notificationService)
    {
        $form = $clientRequestService->newRequest($team);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $clientRequest = $form->getData();

            $errors = $validator->validate($clientRequest);
            if (count($errors) == 0) {
                $em->persist($clientRequest);
                $em->flush();

                $content = $this->renderView('email/client/notificationVerify.html.twig', ['data' => $clientRequest, 'title' => $clientRequest->getTitle(), 'team' => $clientRequest->getTeam()]);
                $notificationService->sendRequestVerify($content, $clientRequest->getEmail());

                return $this->redirectToRoute('client_show', ['slug' => $team->getSlug(), 'token' => $clientRequest->getToken()]);
            }
        }
        return $this->render('client_request/new.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'team' => $team
        ]);
    }

    /**
     * @Route("/client/{slug}/show", name="client_show")
     * @ParamConverter("team", options={"mapping": {"slug": "slug"}})
     */
    public function showRequest(Request $request, Team $team)
    {
        $form = $this->createForm(ClientRequesCommentType::class);
        $clientRequest = $this->getDoctrine()->getRepository(ClientRequest::class)->findOneBy(['token' => $request->get('token')]);

        return $this->render('client_request/show.html.twig', [
            'data' => $clientRequest,
            'team' => $team,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/client/{slug}/comment", name="client_comment")
     * @ParamConverter("team", options={"mapping": {"slug": "slug"}})
     */
    public function clientComment(Request $request, Team $team, ClientRequestService $clientRequestService)
    {
        $data = $request->get('client_reques_comment');
        $clientRequest = $this->getDoctrine()->getRepository(ClientRequest::class)->findOneBy(['token' => $request->get('token')]);

        $content = $data['comment'];
        $clientRequestService->newComment($clientRequest, $content, $clientRequest->getName(), 0);

        return $this->redirectToRoute('client_show', ['slug' => $team->getSlug(), 'token' => $clientRequest->getToken()]);
    }

    /**
     * @Route("/client/{slug}/verify", name="client_valid")
     * @ParamConverter("team", options={"mapping": {"slug": "slug"}})
     */
    public function validateRequest(Request $request, Team $team, NotificationService $notificationService, ClientRequestService $clientRequestService)
    {
        $clientRequest = $this->getDoctrine()->getRepository(ClientRequest::class)->findOneBy(['uuid' => $request->get('token')]);
        if ($clientRequest) {
            $clientRequest->setEmailValid(true);
            $em = $this->getDoctrine()->getManager();
            $em->persist($clientRequest);
            $em->flush();

            $contentInternal = $this->renderView('email/client/notificationNew.html.twig', ['data' => $clientRequest, 'title' => $clientRequest->getTitle(), 'team' => $clientRequest->getTeam()]);
            foreach ($clientRequest->getTeam()->getAdmins() as $admin) {
                $notificationService->sendRequestNew($contentInternal, $admin->getEmail());
            }
            $content = 'Email wurde erfolgreich verifiziert';
            $clientRequestService->newComment($clientRequest, $content, $clientRequest->getName(), 0);

            return $this->redirectToRoute('client_show', ['slug' => $team->getSlug(), 'token' => $clientRequest->getToken()]);
        }
        return $this->redirectToRoute('client_index', ['slug' => $team->getSlug()]);
    }
}
