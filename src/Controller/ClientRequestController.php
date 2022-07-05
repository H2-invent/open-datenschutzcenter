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
use Symfony\Contracts\Translation\TranslatorInterface;

class ClientRequestController extends AbstractController
{
    /**
     * @Route("/client-requests", name="client_requests")
     */
    public function allClientRequests(SecurityService $securityService)
    {
        $team = $this->getUser()->getTeams()[0];
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

        $team = $this->getUser()->getTeams()[0];
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

        $team = $this->getUser()->getTeams()[0];
        if ($securityService->teamDataCheck($clientRequest, $team) === false) {
            return $this->redirectToRoute('client_requests');
        }

        $content = $data['comment'];
        $clientRequestService->newComment($clientRequest, $content, $this->getUser()->getTeams()[0]->getName() . ' > ' . $this->getUser()->getUsername(), 1);
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
     * @Route("/client-requests/internal", name="client_request_make_internal")
     */
    public function makeInternalRequest(TranslatorInterface $translator, SecurityService $securityService, Request $request, ClientRequestService $clientRequestService)
    {
        $clientRequest = $this->getDoctrine()->getRepository(ClientRequest::class)->find($request->get('id'));

        $team = $this->getUser()->getAdminUser();
        if ($securityService->teamDataCheck($clientRequest, $team) === false) {
            return $this->redirectToRoute('client_requests');
        }
        if ($clientRequestService->interalRequest($clientRequest)) {
            $snack = $translator->trans('Änderung gespeichert.');
        } else {
            $snack = $translator->trans('Fehler. Bitte noch einmal versuchen');
        }
        return $this->redirectToRoute('client_requests_show', ['id' => $clientRequest->getId(), 'snack' => $snack]);
    }

    /**
     * @Route("/client-requests/internalNote", name="client_requests_internal_note")
     */
    public function internalNoteClientRequests(SecurityService $securityService, Request $request, ValidatorInterface $validator)
    {

        $team = $this->getUser()->getTeams()[0];
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
    public function editClientRequests(SecurityService $securityService, Request $request, ValidatorInterface $validator, ClientRequestService $clientRequestService, TranslatorInterface $translator)
    {

        $team = $this->getUser()->getAdminUser();
        $clientRequest = $this->getDoctrine()->getRepository(ClientRequest::class)->find($request->get('id'));
        if ($securityService->teamDataCheck($clientRequest, $team) === false) {
            return $this->redirectToRoute('client_requests');
        }
        $content = 'Anfrage wurde geändert || Alte Nachricht: ' . $clientRequest->getTitle() . '| Email: ' . $clientRequest->getEmail() . '| Name: ' . $clientRequest->getName() . '| Grund: ' . $clientRequest->getItemString() . '| Beschreibung: ' . $clientRequest->getDescription() . '| Zusätzliche Angaben: ' . $clientRequest->getFirstname() . ' ' . $clientRequest->getLastname() . ', Geburtstag: ' . $clientRequest->getBirthday()->format('d.m.Y') . ', Adresse: ' . $clientRequest->getStreet() . ' ' . $clientRequest->getCity();

        $form = $this->createForm(ClientRequestType::class, $clientRequest);
        $form->remove('password');
        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $clientRequest = $form->getData();
            $errors = $validator->validate($clientRequest);
            if (count($errors) == 0) {
                $em->persist($clientRequest);
                $em->flush();

                $clientRequestService->newComment($clientRequest, $content, $this->getUser()->getTeams()[0]->getName() . ' > ' . $this->getUser()->getUsername(), 1);

                $snack = $translator->trans('Änderung gespeichert.');
                return $this->redirectToRoute('client_requests_show', ['id' => $clientRequest->getId(), 'snack' => $snack]);
            }
        }
        return $this->render('client_request/internalEdit.html.twig', [
            'data' => $clientRequest,
            'team' => $team,
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/client/{slug}", name="client_index")
     * @ParamConverter("team", options={"mapping": {"slug": "slug"}})
     */
    public function index(Team $team, Request $request, TranslatorInterface $translator)
    {
        $form = $this->createForm(ClientRequestViewType::class);
        $form->handleRequest($request);
        $snack = $request->get('snack');
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $search = $form->getData();
            $pass = sha1($search['password']);
            $clientRequest = $this->getDoctrine()->getRepository(ClientRequest::class)->findOneBy(['uuid' => $search['uuid'], 'email' => $search['email'], 'password' => $pass, 'open' => true]);

            if (count($errors) == 0 && $clientRequest) {
                return $this->redirectToRoute('client_show', ['slug' => $team->getSlug(), 'token' => $clientRequest->getToken()]);
            }
            $snack = $translator->trans('Login war nicht erfolgreich. Bitte versuchen Sie es noch einmal.');
        }

        return $this->render('client_request/index.html.twig', [
            'form' => $form->createView(),
            'team' => $team,
            'snack' => $snack
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
            $clientRequest->setPassword(sha1($clientRequest->getPassword()));

            $errors = $validator->validate($clientRequest);
            if (count($errors) == 0) {
                $em->persist($clientRequest);

                if ($clientRequest->getPgp()) {
                    $content = $this->renderView('email/client/notificationVerifyEncrypt.html.twig', ['data' => $clientRequest]);
                    try {
                        $notificationService->sendEncrypt($clientRequest->getPgp(), $content, $clientRequest->getEmail(), 'Neue Nachricht vom Datenschutzcenter');
                    } catch (\Exception $e) {
                        $clientRequest->setPgp(null);
                        $em->persist($clientRequest);
                    }
                }
                if (!$clientRequest->getPgp()) {
                    $content = $this->renderView('email/client/notificationVerify.html.twig', ['data' => $clientRequest, 'title' => $clientRequest->getTitle(), 'team' => $clientRequest->getTeam()]);
                    $notificationService->sendRequestVerify($content, $clientRequest->getEmail());
                }
                $em->flush();
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
    public function showRequest(Request $request, Team $team, TranslatorInterface $translator)
    {
        $clientRequest = $this->getDoctrine()->getRepository(ClientRequest::class)->findOneBy(['token' => $request->get('token'), 'open' => true]);

        if ($clientRequest) {
            $form = $this->createForm(ClientRequesCommentType::class);
            return $this->render('client_request/show.html.twig', [
                'data' => $clientRequest,
                'team' => $team,
                'form' => $form->createView(),
            ]);
        }
        $snack = $translator->trans('Token passt zu keinem offenen Ticket');
        return $this->redirectToRoute('client_index', ['slug' => $team->getSlug(), 'snack' => $snack]);
    }

    /**
     * @Route("/client/{slug}/comment", name="client_comment")
     * @ParamConverter("team", options={"mapping": {"slug": "slug"}})
     */
    public function clientComment(Request $request, Team $team, ClientRequestService $clientRequestService, TranslatorInterface $translator)
    {
        $data = $request->get('client_reques_comment');
        $clientRequest = $this->getDoctrine()->getRepository(ClientRequest::class)->findOneBy(['token' => $request->get('token')]);

        $content = $data['comment'];
        $clientRequestService->newComment($clientRequest, $content, $clientRequest->getName(), 0);

        $snack = $translator->trans('Kommentar gespeichert');
        return $this->redirectToRoute('client_show', ['slug' => $team->getSlug(), 'token' => $clientRequest->getToken(), 'snack' => $snack]);
    }

    /**
     * @Route("/client/{slug}/verify", name="client_valid")
     * @ParamConverter("team", options={"mapping": {"slug": "slug"}})
     */
    public function validateRequest(Request $request, Team $team, NotificationService $notificationService, ClientRequestService $clientRequestService, TranslatorInterface $translator)
    {
        $clientRequest = $this->getDoctrine()->getRepository(ClientRequest::class)->findOneBy(['uuid' => $request->get('token')]);
        $snack = $translator->trans('Es wurde kein offenes Ticket gefunden.');
        if ($clientRequest) {
            if (!$clientRequest->getEmailValid()) {
                $clientRequest->setEmailValid(true);
                $em = $this->getDoctrine()->getManager();
                $em->persist($clientRequest);
                $em->flush();

                $contentInternal = $this->renderView('email/client/notificationNew.html.twig', ['data' => $clientRequest, 'title' => $clientRequest->getTitle(), 'team' => $clientRequest->getTeam()]);
                foreach ($clientRequest->getTeam()->getAdmins() as $admin) {
                    $notificationService->sendRequestNew($contentInternal, $admin->getEmail());
                }
                $content = 'Email wurde erfolgreich verifiziert';
                $clientRequestService->newComment($clientRequest, $content, $clientRequest->getName(), 1);
                $snack = $translator->trans('Email wurde erfolgreich verifiziert');
            } else {
                $snack = $translator->trans('Email bereits verifiziert');
            }
        }
        return $this->redirectToRoute('client_index', ['slug' => $team->getSlug(), 'snack' => $snack]);
    }
}
