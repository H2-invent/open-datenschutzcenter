<?php

namespace App\Controller;

use App\Entity\AuditTom;
use App\Entity\Datenweitergabe;
use App\Entity\Forms;
use App\Entity\Policies;
use App\Entity\Software;
use App\Entity\Task;
use App\Entity\Vorfall;
use App\Entity\VVT;
use App\Entity\VVTDsfa;
use App\Repository\AuditTomRepository;
use App\Repository\DatenweitergabeRepository;
use App\Repository\FormsRepository;
use App\Repository\PoliciesRepository;
use App\Repository\SoftwareRepository;
use App\Repository\TaskRepository;
use App\Repository\VVTDsfaRepository;
use App\Repository\VVTRepository;
use App\Service\AssignService;
use App\Service\CurrentTeamService;
use App\Service\SecurityService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AssignController extends AbstractController
{
    /**
     * @param CurrentTeamService $currentTeamService
     * @param TaskRepository $taskRepository
     * @return Response
     */
    #[Route(path: '/assign', name: 'assign')]
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

    #[Route(path: '/assign/vvt', name: 'assign_vvt')]
    public function assignVvt(Request $request,
                              AssignService $assignService,
                              SecurityService $securityService,
                              CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $vvt = $this->getDoctrine()->getRepository(VVT::class)->find($request->get('id'));
        if ($securityService->teamDataCheck($vvt, $team) === false) {
            return $this->redirectToRoute('vvt');
        }

        $assignService->assignVvt($request, $vvt);
        return $this->redirect($request->headers->get('referer'));
    }

    #[Route(path: '/assign/audit', name: 'assign_audit')]
    public function assignAudit(Request $request, AssignService $assignService, SecurityService $securityService, CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $audit = $this->getDoctrine()->getRepository(AuditTom::class)->find($request->get('id'));
        if ($securityService->teamDataCheck($audit, $team) === false) {
            return $this->redirectToRoute('audit_tom');
        }

        $assignService->assignAudit($request, $audit);
        return $this->redirect($request->headers->get('referer'));
    }

    #[Route(path: '/assign/daten', name: 'assign_datenweitergabe')]
    public function assignDatenweitergabe(Request $request, AssignService $assignService, SecurityService $securityService, CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $daten = $this->getDoctrine()->getRepository(Datenweitergabe::class)->find($request->get('id'));
        if ($securityService->teamDataCheck($daten, $team) === false) {
            return $this->redirectToRoute('datenweitergabe');
        }

        $assignService->assignDatenweitergabe($request, $daten);
        return $this->redirect($request->headers->get('referer'));
    }

    #[Route(path: '/assign/dsfa', name: 'assign_dsfa')]
    public function assignDsfa(Request            $request,
                               AssignService      $assignService,
                               SecurityService    $securityService,
                               CurrentTeamService $currentTeamService,
                               VVTDsfaRepository  $impactAssessmentRepository
    )
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());;
        $impactAssessment = $impactAssessmentRepository->find($request->get('id'));
        if ($securityService->teamDataCheck($impactAssessment->getVvt(), $team)) {
            $assignService->assignDsfa($request, $impactAssessment);
            return $this->redirect($request->headers->get('referer'));
        }

        return $this->redirectToRoute('vvt');
    }

    #[Route(path: '/assign/form', name: 'assign_form')]
    public function assignForm(Request $request, AssignService $assignService, SecurityService $securityService, CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $form = $this->getDoctrine()->getRepository(Forms::class)->find($request->get('id'));
        if ($securityService->teamDataCheck($form, $team) === false) {
            return $this->redirectToRoute('forms');
        }

        $res = $assignService->assignForm($request, $form);
        return $this->redirect($request->headers->get('referer'));
    }

    #[Route(path: '/assign/policy', name: 'assign_policy')]
    public function assignPolicy(Request $request, AssignService $assignService, SecurityService $securityService, CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $policy = $this->getDoctrine()->getRepository(Policies::class)->find($request->get('id'));
        if ($securityService->teamDataCheck($policy, $team) === false) {
            return $this->redirectToRoute('policies');
        }

        $assignService->assignPolicy($request, $policy);
        return $this->redirect($request->headers->get('referer'));
    }

    #[Route(path: '/assign/software', name: 'assign_software')]
    public function assignSoftware(Request $request, AssignService $assignService, SecurityService $securityService, CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $software = $this->getDoctrine()->getRepository(Software::class)->find($request->get('id'));
        if ($securityService->teamDataCheck($software, $team) === false) {
            return $this->redirectToRoute('software');
        }

        $assignService->assignSoftware($request, $software);
        return $this->redirect($request->headers->get('referer'));
    }

    #[Route(path: '/assign/vorfall', name: 'assign_vorfall')]
    public function assignVorfall(Request $request, AssignService $assignService, SecurityService $securityService, CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $vorfall = $this->getDoctrine()->getRepository(Vorfall::class)->find($request->get('id'));
        if ($securityService->teamDataCheck($vorfall, $team) === false) {
            return $this->redirectToRoute('vorfall');
        }

        $assignService->assignVorfall($request, $vorfall);
        return $this->redirect($request->headers->get('referer'));
    }

    #[Route(path: '/assign/task', name: 'assign_task')]
    public function assignTask(Request $request, AssignService $assignService, SecurityService $securityService, CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $task = $this->getDoctrine()->getRepository(Task::class)->find($request->get('id'));
        if ($securityService->teamDataCheck($task, $team) === false) {
            return $this->redirectToRoute('tasks');
        }

        $assignService->assignTask($request, $task);
        return $this->redirect($request->headers->get('referer'));
    }
}
