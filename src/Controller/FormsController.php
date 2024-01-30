<?php

namespace App\Controller;

use App\Entity\Forms;
use App\Entity\User;
use App\Repository\FormsRepository;
use App\Service\ApproveService;
use App\Service\AssignService;
use App\Service\CurrentTeamService;
use App\Service\DisableService;
use App\Service\FormsService;
use App\Service\SecurityService;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FormsController extends BaseController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private EntityManagerInterface       $em,
    ) {
    }

    #[Route(path: '/forms/new', name: 'forms_new')]
    public function addForms(
        ValidatorInterface $validator,
        Request            $request,
        FormsService       $formsService,
        SecurityService    $securityService,
        CurrentTeamService $currentTeamService,
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $team = $currentTeamService->getTeamFromSession($user);

        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $daten = $formsService->newForm($user);

        $form = $formsService->createForm($daten, $team);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $daten = $form->getData();
            $errors = $validator->validate($daten);

            if (count($errors) == 0) {
                $this->em->persist($daten);
                $this->em->flush();

                return $this->redirectToRoute('forms');
            }
        }

        $this->setBackButton($this->generateUrl('forms'));

        return $this->render('forms/new.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => $this->translator->trans(id: 'form.create', domain: 'forms'),
            'daten' => $daten,
            'activ' => $daten->getActiv(),
        ]);
    }

    #[Route(path: '/forms/approve', name: 'forms_approve')]
    public function approvePolicy(
        Request            $request,
        SecurityService    $securityService,
        ApproveService     $approveService,
        CurrentTeamService $currentTeamService,
        FormsRepository    $formsRepository,
    ): Response
    {
        $user = $this->getUser();
        $team = $currentTeamService->getTeamFromSession($user);
        $forms = $formsRepository->find($request->get('id'));

        if ($securityService->teamDataCheck($forms, $team) && $securityService->adminCheck($user, $team)) {
            $approve = $approveService->approve($forms, $user);
            $this->addSuccessMessage($approve['snack']);
            return $this->redirectToRoute('forms_edit', ['id' => $approve['data']]);
        }

        // if security check fails
        return $this->redirectToRoute('forms');
    }

    #[Route(path: '/forms/disable', name: 'forms_disable')]
    public function disable(
        Request            $request,
        SecurityService    $securityService,
        DisableService     $disableService,
        CurrentTeamService $currentTeamService,
        FormsRepository    $formsRepository,
    ): Response
    {
        $user = $this->getUser();
        $team = $currentTeamService->getTeamFromSession($user);
        $forms = $formsRepository->find($request->get('id'));

        if ($securityService->teamDataCheck($forms, $team) && $securityService->adminCheck($user, $team) && !$forms->getApproved()) {
            $disableService->disable($forms, $user);
        }

        return $this->redirectToRoute('forms');
    }

    #[Route(path: '/forms/download/{id}', name: 'forms_download_file', methods: ['GET'])]
    #[ParamConverter('forms', options: ['mapping' => ['id' => 'id']])]
    public function downloadArticleReference(
        FilesystemOperator $formsFilesystem,
        Forms               $forms,
        SecurityService     $securityService,
        LoggerInterface     $logger,
        CurrentTeamService  $currentTeamService,
    ): Response
    {
        $stream = $formsFilesystem->read($forms->getUpload());
        /** @var User $user */
        $user = $this->getUser();

        $team = $currentTeamService->getTeamFromSession($user);
        if ($securityService->teamDataCheck($forms, $team) === false) {
            $logger->error(
                'DOWNLOAD',
                [
                    'typ' => 'DOWNLOAD',
                    'error' => true,
                    'hinweis' => $this->translator->trans(id: 'user.unauthorized', domain: 'forms'),
                    'dokument' => $forms->getUpload(),
                    'user' => $user->getUsername(),
                ],
            );
            return $this->redirectToRoute('dashboard');
        }

        $type = $formsFilesystem->mimeType($forms->getUpload());
        $response = new Response($stream);
        $response->headers->set('Content-Type', $type);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $forms->getUpload())
        );

        $response->headers->set('Content-Disposition', $disposition);
        $logger->info(
            'DOWNLOAD',
            [
                'typ' => 'DOWNLOAD',
                'error' => false,
                'hinweis' => $this->translator->trans(id: 'download.successful', domain: 'general'),
                'dokument' => $forms->getUpload(),
                'user' => $user->getUsername(),
            ],
        );
        return $response;
    }

    #[Route(path: '/forms/edit', name: 'forms_edit')]
    public function editFormulare(
        ValidatorInterface $validator,
        Request            $request,
        SecurityService    $securityService,
        FormsService       $formsService,
        AssignService      $assignService,
        CurrentTeamService $currentTeamService,
        FormsRepository    $formsRepository,
    ): Response
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $forms = $formsRepository->find($request->get('id'));

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
                $this->em->persist($newForms);
                $this->em->persist($forms);
                $this->em->flush();
                $this->addSuccessMessage($this->translator->trans(id: 'save.successful', domain: 'general'));

                return $this->redirectToRoute(
                    'forms_edit',
                    [
                        'id' => $newForms->getId(),
                    ]
                );
            }
        }

        $this->setBackButton($this->generateUrl('forms'));

        return $this->render('forms/edit.html.twig', [
            'form' => $form->createView(),
            'assignForm' => $assign->createView(),
            'errors' => $errors,
            'title' => $this->translator->trans(id: 'form.edit', domain: 'forms'),
            'daten' => $forms,
            'activ' => $forms->getActiv(),
        ]);
    }

    #[Route(path: '/forms', name: 'forms')]
    public function indexForms(
        SecurityService    $securityService,
        CurrentTeamService $currentTeamService,
        FormsRepository    $formsRepository,
    ): Response
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $daten = $formsRepository->findBy(['team' => $team, 'activ' => true]);
        return $this->render('forms/index.html.twig', [
            'table' => $daten,
            'titel' => $this->translator->trans(id: 'form.word', domain: 'forms'),
            'currentTeam' => $team,
        ]);
    }
}
