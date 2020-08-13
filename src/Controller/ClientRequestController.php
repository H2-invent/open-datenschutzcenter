<?php

namespace App\Controller;

use App\Entity\ClientComment;
use App\Entity\ClientRequest;
use App\Entity\Team;
use App\Form\Type\ClientRequesCommentType;
use App\Form\Type\ClientRequestType;
use App\Form\Type\ClientRequestViewType;
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
        $client = $this->getDoctrine()->getRepository(ClientRequest::class)->findActivByTeam($team);

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
        ]);
    }

    /**
     * @Route("/client-requests/comment", name="client_request_comment")
     */
    public function clientRequestComment(SecurityService $securityService, Request $request)
    {
        $clientComment = new ClientComment();

        $data = $request->get('client_reques_comment');
        $clientRequest = $this->getDoctrine()->getRepository(ClientRequest::class)->find($request->get('clientRequest'));

        $team = $this->getUser()->getTeam();
        if ($securityService->teamDataCheck($clientRequest, $team) === false) {
            return $this->redirectToRoute('client_requests');
        }

        if ($this->getUser()) {
            $clientComment->setName($this->getUser()->getUsername());
            $clientComment->setInternal(true);
        } else {
            $clientComment->setName($clientRequest->getName());
            $clientComment->setInternal(false);
        }
        $clientComment->setComment($data['comment']);
        $clientComment->setClientRequest($clientRequest);
        $clientComment->setCreatedAt(new \DateTime());
        $em = $this->getDoctrine()->getManager();
        $em->persist($clientComment);
        $em->flush();
        return $this->redirectToRoute('client_requests_show', ['id' => $clientRequest->getId()]);
    }

    /**
     * @Route("/client/{id}", name="client_index")
     * @ParamConverter("team", options={"mapping": {"id": "id"}})
     */
    public function index(Team $team)
    {
        $form = $this->createForm(ClientRequestViewType::class);

        return $this->render('client_request/index.html.twig', [
            'form' => $form->createView(),
            'team' => $team
        ]);
    }

    /**
     * @Route("/client/{id}/new", name="client_new")
     * @ParamConverter("team", options={"mapping": {"id": "id"}})
     */
    public function newRequest(Request $request, ValidatorInterface $validator, Team $team)
    {
        $clientRequest = new ClientRequest();
        $clientRequest->setUuid(uniqid());
        $clientRequest->setCreatedAt(new \DateTime());
        $clientRequest->setEmailValid(false);
        $clientRequest->setToken(md5(uniqid()));
        $clientRequest->setTeam($team);
        $clientRequest->setActiv(true);

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
                return $this->redirectToRoute('client_show', ['id' => $team->getId(), 'token' => $clientRequest->getToken()]);
            }
        }
        return $this->render('client_request/new.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'team' => $team
        ]);
    }

    /**
     * @Route("/client/{id}/show", name="client_show")
     * @ParamConverter("team", options={"mapping": {"id": "id"}})
     */
    public function showRequest(Request $request, Team $team)
    {
        $data = $request->get('client_request_view');

        if ($data != null) {
            $clientRequest = $this->getDoctrine()->getRepository(ClientRequest::class)->findOneBy(['uuid' => $data['uuid']]);
            if (!$clientRequest) {
                return $this->redirectToRoute('client_index');
            }
            if ($clientRequest->getEmail() !== $data['email']) {
                return $this->redirectToRoute('client_index');
            }
        } else {
            $clientRequest = $this->getDoctrine()->getRepository(ClientRequest::class)->findOneBy(['token' => $request->get('token')]);
        }

        return $this->render('client_request/show.html.twig', [
            'data' => $clientRequest,
            'team' => $team
        ]);
    }

    /**
     * @Route("/client/{id}/comment", name="client_comment")
     * @ParamConverter("team", options={"mapping": {"id": "id"}})
     */
    public function clientComment(Request $request, Team $team)
    {
        $clientComment = new ClientComment();

        $data = $request->get('client_reques_comment');
        $clientRequest = $this->getDoctrine()->getRepository(ClientRequest::class)->find($request->get('clientRequest'));

        $clientComment->setName($clientRequest->getName());
        $clientComment->setInternal(false);
        $clientComment->setComment($data['comment']);
        $clientComment->setClientRequest($clientRequest);
        $clientComment->setCreatedAt(new \DateTime());

        $em = $this->getDoctrine()->getManager();
        $em->persist($clientComment);
        $em->flush();
        return $this->redirectToRoute('client_show', ['id' => $team->getId(), 'token' => $clientRequest->getToken()]);
    }

    /**
     * @Route("/client/v", name="client_valid")
     */
    public function validateRequest(Request $request)
    {
        $clientRequest = $this->getDoctrine()->getRepository(ClientRequest::class)->findOneBy(['token' => $request->get('token')]);

        if ($clientRequest) {
            $clientRequest->setEmailValid(true);
            $em = $this->getDoctrine()->getManager();
            $em->persist($clientRequest);
            $em->flush();
            return $this->redirectToRoute('client_show', ['token' => $clientRequest->getToken()]);
        }

        return $this->redirectToRoute('client_index', ['id' => $clientRequest->getId()]);

    }
}
