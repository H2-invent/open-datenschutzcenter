<?php

namespace App\Controller;

use App\Repository\AuditTomRepository;
use App\Repository\DatenweitergabeRepository;
use App\Repository\FormsRepository;
use App\Repository\PoliciesRepository;
use App\Repository\SoftwareRepository;
use App\Repository\TaskRepository;
use App\Repository\VorfallRepository;
use App\Repository\VVTDsfaRepository;
use App\Repository\VVTRepository;
use App\Service\AssignService;
use App\Service\CurrentTeamService;
use App\Service\SecurityService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/assign', name: 'assign')]
class AssignController extends AbstractController
{
    #[Route(path: '/audit', name: '_audit')]
    public function assignAudit(
        Request            $request,
        AssignService      $assignService,
        SecurityService    $securityService,
        CurrentTeamService $currentTeamService,
        AuditTomRepository $auditTomRepository,
    ): Response
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $audit = $auditTomRepository->find($request->get('id'));
        if ($securityService->teamDataCheck($audit, $team) === false) {
            return $this->redirectToRoute('audit_tom');
        }

        $assignService->assignAudit($request, $audit);
        return $this->redirect($request->headers->get('referer'));
    }

    #[Route(path: '/daten', name: '_datenweitergabe')]
    public function assignDataTransfer(
        Request                   $request,
        AssignService             $assignService,
        SecurityService           $securityService,
        CurrentTeamService        $currentTeamService,
        DatenweitergabeRepository $dataTransferRepository,
    ): Response
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $daten = $dataTransferRepository->find($request->get('id'));
        if ($securityService->teamDataCheck($daten, $team) === false) {
            return $this->redirectToRoute('datenweitergabe');
        }

        $assignService->assignDatenweitergabe($request, $daten);
        return $this->redirect($request->headers->get('referer'));
    }

    #[Route(path: '/dsfa', name: '_dsfa')]
    public function assignDsfa(
        Request            $request,
        AssignService      $assignService,
        SecurityService    $securityService,
        CurrentTeamService $currentTeamService,
        VVTDsfaRepository  $impactAssessmentRepository
    ): Response
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $impactAssessment = $impactAssessmentRepository->find($request->get('id'));
        if ($securityService->teamDataCheck($impactAssessment->getVvt(), $team)) {
            $assignService->assignDsfa($request, $impactAssessment);
            return $this->redirect($request->headers->get('referer'));
        }

        return $this->redirectToRoute('vvt');
    }

    #[Route(path: '/form', name: '_form')]
    public function assignForm(
        Request            $request,
        AssignService      $assignService,
        SecurityService    $securityService,
        CurrentTeamService $currentTeamService,
        FormsRepository    $formsRepository,
    ): Response
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $form = $formsRepository->find($request->get('id'));
        if ($securityService->teamDataCheck($form, $team) === false) {
            return $this->redirectToRoute('forms');
        }

        $res = $assignService->assignForm($request, $form);
        return $this->redirect($request->headers->get('referer'));
    }

    #[Route(path: '/policy', name: '_policy')]
    public function assignPolicy(
        Request            $request,
        AssignService      $assignService,
        SecurityService    $securityService,
        CurrentTeamService $currentTeamService,
        PoliciesRepository $policiesRepository,
    ): Response
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $policy = $policiesRepository->find($request->get('id'));
        if ($securityService->teamDataCheck($policy, $team) === false) {
            return $this->redirectToRoute('policies');
        }

        $assignService->assignPolicy($request, $policy);
        return $this->redirect($request->headers->get('referer'));
    }

    #[Route(path: '/software', name: '_software')]
    public function assignSoftware(
        Request            $request,
        AssignService      $assignService,
        SecurityService    $securityService,
        CurrentTeamService $currentTeamService,
        SoftwareRepository $softwareRepository,
    ): Response
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $software = $softwareRepository->find($request->get('id'));
        if ($securityService->teamDataCheck($software, $team) === false) {
            return $this->redirectToRoute('software');
        }

        $assignService->assignSoftware($request, $software);
        return $this->redirect($request->headers->get('referer'));
    }

    #[Route(path: '/task', name: '_task')]
    public function assignTask(
        Request            $request,
        AssignService      $assignService,
        SecurityService    $securityService,
        CurrentTeamService $currentTeamService,
        TaskRepository     $taskRepository,
    ): Response
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $task = $taskRepository->find($request->get('id'));
        if ($securityService->teamDataCheck($task, $team) === false) {
            return $this->redirectToRoute('tasks');
        }

        $assignService->assignTask($request, $task);
        return $this->redirect($request->headers->get('referer'));
    }

    #[Route(path: '/vorfall', name: '_vorfall')]
    public function assignVorfall(
        Request            $request,
        AssignService      $assignService,
        SecurityService    $securityService,
        CurrentTeamService $currentTeamService,
        VorfallRepository  $vorfallRepository,
    ): Response
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $vorfall = $vorfallRepository->find($request->get('id'));
        if ($securityService->teamDataCheck($vorfall, $team) === false) {
            return $this->redirectToRoute('vorfall');
        }

        $assignService->assignVorfall($request, $vorfall);
        return $this->redirect($request->headers->get('referer'));
    }

    #[Route(path: '/vvt', name: '_vvt')]
    public function assignVvt(
        Request            $request,
        AssignService      $assignService,
        SecurityService    $securityService,
        CurrentTeamService $currentTeamService,
        VVTRepository      $vvtRepository,
    ): Response
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $vvt = $vvtRepository->find($request->get('id'));
        if ($securityService->teamDataCheck($vvt, $team) === false) {
            return $this->redirectToRoute('vvt');
        }

        $assignService->assignVvt($request, $vvt);
        return $this->redirect($request->headers->get('referer'));
    }

    #[Route(path: '', name: '')]
    public function index(CurrentTeamService        $currentTeamService,
                          DatenweitergabeRepository $transferRepository,
                          VVTRepository             $processingRepository,
                          AuditTomRepository        $auditRepository,
                          VVTDsfaRepository         $impactAssessmentRepository,
                          FormsRepository           $formRepository,
                          PoliciesRepository        $policyRepository,
                          SoftwareRepository        $softwareRepository,
                          TaskRepository            $taskRepository
    ): Response
    {
        $user = $this->getUser();
        $currentTeam = $currentTeamService->getTeamFromSession($user);
        $assignedDataTransfers = $transferRepository->findActiveByTeamAndUser($currentTeam, $user);
        $assignedProcessings = $processingRepository->findActiveByTeamAndUser($currentTeam, $user);
        $assignedAudits = $auditRepository->findActiveByTeamAndUser($currentTeam, $user);
        $assignImpactAssessments = $impactAssessmentRepository->findActiveByTeamAndUser($currentTeam, $user);
        $assignedForms = $formRepository->findActiveByTeamAndUser($currentTeam, $user);
        $assignedPolicies = $policyRepository->findActiveByTeamAndUser($currentTeam, $user);
        $assignedSoftware = $softwareRepository->findActiveByTeamAndUser($currentTeam, $user);
        $assignedTasks = $taskRepository->findActiveByTeamAndUser($currentTeam, $user);

        return $this->render('assign/index.html.twig', [
            'currentTeam' => $currentTeam,
            'dataTransfers' => $assignedDataTransfers,
            'processings' => $assignedProcessings,
            'audits' => $assignedAudits,
            'impactAssessments' => $assignImpactAssessments,
            'forms' => $assignedForms,
            'policies' => $assignedPolicies,
            'software' => $assignedSoftware,
            'tasks' => $assignedTasks
        ]);
    }
}
