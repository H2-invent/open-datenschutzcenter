<?php

namespace App\Controller;

use App\Entity\Forms;
use App\Repository\FormsRepository;
use App\Service\ApproveService;
use App\Service\AssignService;
use App\Service\CurrentTeamService;
use App\Service\DisableService;
use App\Service\FormsService;
use App\Service\SecurityService;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FormsController extends AbstractController
{
    #[Route(path: '/forms', name: 'forms')]
    public function indexForms(SecurityService $securityService,
                               FormsRepository $formRepository,
                               CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $data = $formRepository->findBy(array('team' => $team, 'activ' => true));
        return $this->render('forms/index.html.twig', [
            'table' => $data,
            'titel' => 'Formulare',
            'currentTeam' => $team,
        ]);
    }

    #[Route(path: '/forms/new', name: 'forms_new')]
    public function addForms(ValidatorInterface $validator,
                             Request $request,
                             EntityManagerInterface $entityManager,
                             FormsService $formsService,
                             SecurityService $securityService,
                             CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $data = $formsService->newForm($this->getUser());

        $form = $formsService->createForm($data, $team);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $errors = $validator->validate($data);
            if (count($errors) == 0) {
                $entityManager->persist($data);
                $entityManager->flush();
                return $this->redirectToRoute('forms');
            }
        }
        return $this->render('forms/new.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'Formular erstellen/hochladen',
            'daten' => $data,
            'activ' => $data->getActiv()
        ]);
    }

    #[Route(path: '/forms/edit', name: 'forms_edit')]
    public function EditFormulare(ValidatorInterface $validator,
                                  Request $request,
                                  EntityManagerInterface $entityManager,
                                  FormsRepository $formRepository,
                                  SecurityService $securityService,
                                  FormsService $formsService,
                                  AssignService $assignService,
                                  CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $forms = $formRepository->find($request->get('id'));

        if ($securityService->teamDataCheck($forms, $team) === false) {
            return $this->redirectToRoute('forms');
        }

        $newForms = $formsService->cloneForms($forms, $this->getUser());
        $form = $formsService->createForm($newForms, $team);
        $form->handleRequest($request);
        $assign = $assignService->createForm($forms, $team);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid() && $forms->getActiv() && !$forms->getApproved()) {
            $forms->setActiv(false);
            $forms->setStatus(4);
            $newForms = $form->getData();

            $errors = $validator->validate($newForms);
            if (count($errors) == 0) {

                $entityManager->persist($newForms);
                $entityManager->persist($forms);
                $entityManager->flush();
                return $this->redirectToRoute('forms_edit', array('id' => $newForms->getId(), 'snack' => 'Erfolgreich gespeichert'));
            }
        }
        return $this->render('forms/edit.html.twig', [
            'form' => $form->createView(),
            'assignForm' => $assign->createView(),
            'errors' => $errors,
            'title' => 'Formular bearbeiten',
            'daten' => $forms,
            'activ' => $forms->getActiv(),
            'snack' => $request->get('snack')
        ]);
    }

    #[Route(path: '/forms/approve', name: 'forms_approve')]
    public function approvePolicy(Request $request,
                                  FormsRepository $formRepository,
                                  SecurityService $securityService,
                                  ApproveService $approveService,
                                  CurrentTeamService $currentTeamService)
    {
        $user = $this->getUser();
        $team = $currentTeamService->getTeamFromSession($user);
        $forms = $formRepository->find($request->get('id'));

        if ($securityService->teamDataCheck($forms, $team) && $securityService->adminCheck($user, $team)) {
            $approve = $approveService->approve($forms, $user);
            return $this->redirectToRoute('forms_edit', ['id' => $approve['data'], 'snack' => $approve['snack']]);
        }

        // if security check fails
        return $this->redirectToRoute('forms');
    }

    #[Route(path: '/forms/disable', name: 'forms_disable')]
    public function disable(Request $request,
                            FormsRepository $formRepository,
                            SecurityService $securityService,
                            DisableService $disableService,
                            CurrentTeamService $currentTeamService)
    {
        $user = $this->getUser();
        $team = $currentTeamService->getTeamFromSession($user);
        $forms = $formRepository->find($request->get('id'));

        if ($securityService->teamDataCheck($forms, $team) && $securityService->adminCheck($user, $team) && !$forms->getApproved()) {
            $disableService->disable($forms, $user);
        }

        return $this->redirectToRoute('forms');
    }

    #[Route(path: '/forms/download/{id}', name: 'forms_download_file', methods: ['GET'])]
    #[ParamConverter('forms', options: ['mapping' => ['id' => 'id']])]
    public function downloadArticleReference(FilesystemInterface $formsFileSystem,
                                             Forms $forms,
                                             SecurityService $securityService,
                                             LoggerInterface $logger,
                                             CurrentTeamService $currentTeamService)
    {

        $stream = $formsFileSystem->read($forms->getUpload());

        $team = $currentTeamService->getTeamFromSession($this->getUser());
        if ($securityService->teamDataCheck($forms, $team) === false) {
            $message = ['typ' => 'DOWNLOAD', 'error' => true, 'hinweis' => 'Fehlerhafter download. User nicht berechtigt!', 'dokument' => $forms->getUpload(), 'user' => $this->getUser()->getUsername()];
            $logger->error($message['typ'], $message);
            return $this->redirectToRoute('dashboard');
        }

        $type = $formsFileSystem->getMimetype($forms->getUpload());
        $response = new Response($stream);
        $response->headers->set('Content-Type', $type);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $forms->getUpload())
        );

        $response->headers->set('Content-Disposition', $disposition);
        $message = ['typ' => 'DOWNLOAD', 'error' => false, 'hinweis' => 'Download erfolgreich', 'dokument' => $forms->getUpload(), 'user' => $this->getUser()->getUsername()];
        $logger->info($message['typ'], $message);
        return $response;
    }
}
