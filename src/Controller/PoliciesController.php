<?php

namespace App\Controller;

use App\Entity\Policies;
use App\Repository\PoliciesRepository;
use App\Repository\TeamRepository;
use App\Service\ApproveService;
use App\Service\AssignService;
use App\Service\CurrentTeamService;
use App\Service\DisableService;
use App\Service\PoliciesService;
use App\Service\SecurityService;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PoliciesController extends AbstractController
{


    public function __construct(private readonly TranslatorInterface $translator,
                                private EntityManagerInterface       $em,
    )
    {
    }

    #[Route(path: '/policy/new', name: 'policy_new')]
    public function addPolicy(
        ValidatorInterface $validator,
        Request            $request,
        PoliciesService    $policiesService,
        SecurityService    $securityService,
        CurrentTeamService $currentTeamService,
    )
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());

        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('policies');
        }

        $policy = $policiesService->newPolicy($team, $this->getUser());
        $form = $policiesService->createForm($policy, $team);
        $form->handleRequest($request);


        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $policy = $form->getData();
            $errors = $validator->validate($policy);
            if (count($errors) == 0) {
                $this->em->persist($policy);
                $this->em->flush();
                return $this->redirectToRoute('policies');
            }
        }
        return $this->render('policies/new.html.twig', [
            'form' => $form->createView(),
            'policy' => $policy,
            'errors' => $errors,
            'title' => $this->translator->trans(id: 'policies.create', domain: 'policies'),
            'activNummer' => true,
            'vvt' => $policy,
            'activ' => $policy->getActiv(),
            'CTA' => false,
        ]);
    }

    #[Route(path: '/policy/approve', name: 'policy_approve')]
    public function approvePolicy(
        Request            $request,
        SecurityService    $securityService,
        ApproveService     $approveService,
        CurrentTeamService $currentTeamService,
        PoliciesRepository $policiesRepository,
    ): Response
    {
        $user = $this->getUser();
        $team = $currentTeamService->getCurrentTeam($user);
        $policy = $policiesRepository->find($request->get('id'));

        if ($securityService->teamDataCheck($policy, $team) && $securityService->adminCheck($user, $team)) {
            $approve = $approveService->approve($policy, $user);
            return $this->redirectToRoute('policy_edit', ['id' => $approve['data'], 'snack' => $approve['snack']]);
        }

        // if security check fails
        return $this->redirectToRoute('policies');
    }

    #[Route(path: '/policy/disable', name: 'policy_disable')]
    public function disable(
        Request            $request,
        SecurityService    $securityService,
        DisableService     $disableService,
        CurrentTeamService $currentTeamService,
        PoliciesRepository $policiesRepository,
    ): Response
    {
        $user = $this->getUser();
        $team = $currentTeamService->getCurrentTeam($user);
        $policy = $policiesRepository->find($request->get('id'));

        if ($securityService->teamDataCheck($policy, $team) && $securityService->adminCheck($user, $team) && !$policy->getApproved()) {
            $disableService->disable($policy, $this->getUser());
        }

        return $this->redirectToRoute('policies');
    }

    #[Route(path: '/policy/download/{id}', name: 'policy_download_file', methods: ['GET'])]
    #[ParamConverter('policies', options: ['mapping' => ['id' => 'id']])]
    public function downloadArticleReference(
        FilesystemOperator $policiesFilesystem,
        Policies            $policies,
        SecurityService     $securityService,
        LoggerInterface     $logger,
        CurrentTeamService  $currentTeamService,
    ): Response
    {

        $stream = $policiesFilesystem->read($policies->getUpload());

        $team = $currentTeamService->getCurrentTeam($this->getUser());
        if ($securityService->teamDataCheck($policies, $team) === false) {
            $logger->error(
                'DOWNLOAD',
                [
                    'typ' => 'DOWNLOAD',
                    'error' => true,
                    'hinweis' => 'Fehlerhafter download. User nicht berechtigt!',
                    'dokument' => $policies->getUpload(),
                    'user' => $this->getUser()->getUsername()
                ],
            );
            return $this->redirectToRoute('dashboard');
        }

        $type = $policiesFilesystem->getMimetype($policies->getUpload());
        $response = new Response($stream);
        $response->headers->set('Content-Type', $type);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $policies->getUpload())
        );

        $response->headers->set('Content-Disposition', $disposition);
        $logger->info(
            'DOWNLOAD',
            [
                'typ' => 'DOWNLOAD',
                'error' => false,
                'hinweis' => 'Download erfolgreich',
                'dokument' => $policies->getUpload(),
                'user' => $this->getUser()->getUsername()
            ],
        );
        return $response;
    }

    #[Route(path: '/policy/edit', name: 'policy_edit')]
    public function editPolicy(
        ValidatorInterface $validator,
        Request            $request,
        PoliciesService    $policiesService,
        SecurityService    $securityService,
        AssignService      $assignService,
        CurrentTeamService $currentTeamService,
        PoliciesRepository $policiesRepository,
        TeamRepository     $teamRepository,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        $teamPath = $team ? $teamRepository->getPath($team) : null;
        $policy = $policiesRepository->find($request->get('id'));

        if ($securityService->teamPathDataCheck($policy, $teamPath) === false) {
            return $this->redirectToRoute('policies');
        }
        $newPolicy = $policiesService->clonePolicy($policy, $this->getUser());
        $isEditable = $policy->getTeam() === $team;
        $form = $policiesService->createForm($newPolicy, $team, ['disabled' => !$isEditable]);
        $form->handleRequest($request);
        $assign = $assignService->createForm($policy, $team, ['disabled' => !$isEditable]);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid() && $policy->getActiv() && !$policy->getApproved() && $isEditable) {
            $policy->setActiv(false);
            $newPolicy = $form->getData();
            $errors = $validator->validate($newPolicy);
            if (count($errors) == 0) {
                $this->em->persist($newPolicy);
                $this->em->persist($policy);
                $this->em->flush();
                return $this->redirectToRoute(
                    'policy_edit',
                    [
                        'id' => $newPolicy->getId(),
                        'snack' => $this->translator->trans(id: 'save.successful', domain: 'general'),
                    ],
                );
            }
        }

        return $this->render('policies/edit.html.twig', [
            'form' => $form->createView(),
            'assignForm' => $assign->createView(),
            'errors' => $errors,
            'title' => $this->translator->trans(id: 'policies.edit', domain: 'policies'),
            'policy' => $policy,
            'activ' => $policy->getActiv(),
            'snack' => $request->get('snack'),
            'isEditable' => $isEditable,
        ]);
    }

    #[Route(path: '/policies', name: 'policies')]
    public function index(
        SecurityService    $securityService,
        CurrentTeamService $currentTeamService,
        PoliciesRepository $policiesRepository,
        TeamRepository     $teamRepository,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $teamPath = $teamRepository->getPath($team);
        $policies = $policiesRepository->findActiveByTeamPath($teamPath);

        return $this->render('policies/index.html.twig', [
            'data' => $policies,
            'currentTeam' => $team,
        ]);
    }
}
