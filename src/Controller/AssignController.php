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
use App\Service\AssignService;
use App\Service\SecurityService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AssignController extends AbstractController
{
    /**
     * @Route("/assign", name="assign")
     */
    public function index(Request $request, AssignService $assignService)
    {

        $assignDatenweitergabe = $this->getUser()->getAssignedDatenweitergaben()->toarray();
        $assignVvt = $this->getUser()->getAssignedVvts()->toarray();
        $assignAudit = $this->getUser()->getAssignedAudits()->toarray();
        $assignDsfa = $this->getUser()->getAssignedDsfa()->toarray();
        $assignForms = $this->getUser()->getAssignedForms()->toarray();
        $assignPolicies = $this->getUser()->getAssignedPolicies()->toarray();
        $assignSoftware = $this->getUser()->getAssignedSoftware()->toarray();
        $assignTasks = $this->getDoctrine()->getRepository(Task::class)->findActivByUser($this->getUser());

        return $this->render('assign/index.html.twig', [
            'assignDaten' => $assignDatenweitergabe,
            'assignVvt' => $assignVvt,
            'assignAudit' => $assignAudit,
            'assignDsfa' => $assignDsfa,
            'assignForms' => $assignForms,
            'assignPolicies' => $assignPolicies,
            'assignSoftware' => $assignSoftware,
            'assignTasks' => $assignTasks
        ]);
    }

    /**
     * @Route("/assign/vvt", name="assign_vvt")
     */
    public function assignVvt(Request $request, AssignService $assignService, SecurityService $securityService)
    {
        $team = $this->getUser()->getTeams()[0];
        $vvt = $this->getDoctrine()->getRepository(VVT::class)->find($request->get('id'));
        if ($securityService->teamDataCheck($vvt, $team) === false) {
            return $this->redirectToRoute('vvt');
        }

        $res = $assignService->assignVvt($request, $vvt);
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/assign/audit", name="assign_audit")
     */
    public function assignAudit(Request $request, AssignService $assignService, SecurityService $securityService)
    {
        $team = $this->getUser()->getTeams()[0];
        $audit = $this->getDoctrine()->getRepository(AuditTom::class)->find($request->get('id'));
        if ($securityService->teamDataCheck($audit, $team) === false) {
            return $this->redirectToRoute('audit_tom');
        }

        $res = $assignService->assignAudit($request, $audit);
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/assign/daten", name="assign_datenweitergabe")
     */
    public function assignDatenweitergabe(Request $request, AssignService $assignService, SecurityService $securityService)
    {
        $team = $this->getUser()->getTeams()[0];
        $daten = $this->getDoctrine()->getRepository(Datenweitergabe::class)->find($request->get('id'));
        if ($securityService->teamDataCheck($daten, $team) === false) {
            return $this->redirectToRoute('datenweitergabe');
        }

        $res = $assignService->assignDatenweitergabe($request, $daten);
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/assign/dsfa", name="assign_dsfa")
     */
    public function assignDsfa(Request $request, AssignService $assignService, SecurityService $securityService)
    {
        $team = $this->getUser()->getTeams()[0];
        $dsfa = $this->getDoctrine()->getRepository(VVTDsfa::class)->find($request->get('id'));
        if ($securityService->teamDataCheck($dsfa->getVvt(), $team) === false) {
            return $this->redirectToRoute('vvt');
        }

        $res = $assignService->assignDsfa($request, $dsfa);
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/assign/form", name="assign_form")
     */
    public function assignForm(Request $request, AssignService $assignService, SecurityService $securityService)
    {
        $team = $this->getUser()->getTeams()[0];
        $form = $this->getDoctrine()->getRepository(Forms::class)->find($request->get('id'));
        if ($securityService->teamDataCheck($form, $team) === false) {
            return $this->redirectToRoute('forms');
        }

        $res = $assignService->assignForm($request, $form);
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/assign/policy", name="assign_policy")
     */
    public function assignPolicy(Request $request, AssignService $assignService, SecurityService $securityService)
    {
        $team = $this->getUser()->getTeams()[0];
        $policy = $this->getDoctrine()->getRepository(Policies::class)->find($request->get('id'));
        if ($securityService->teamDataCheck($policy, $team) === false) {
            return $this->redirectToRoute('policies');
        }

        $res = $assignService->assignPolicy($request, $policy);
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/assign/software", name="assign_software")
     */
    public function assignSoftware(Request $request, AssignService $assignService, SecurityService $securityService)
    {
        $team = $this->getUser()->getTeams()[0];
        $software = $this->getDoctrine()->getRepository(Software::class)->find($request->get('id'));
        if ($securityService->teamDataCheck($software, $team) === false) {
            return $this->redirectToRoute('software');
        }

        $res = $assignService->assignSoftware($request, $software);
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/assign/vorfall", name="assign_vorfall")
     */
    public function assignVorfall(Request $request, AssignService $assignService, SecurityService $securityService)
    {
        $team = $this->getUser()->getTeams()[0];
        $vorfall = $this->getDoctrine()->getRepository(Vorfall::class)->find($request->get('id'));
        if ($securityService->teamDataCheck($vorfall, $team) === false) {
            return $this->redirectToRoute('vorfall');
        }

        $res = $assignService->assignVorfall($request, $vorfall);
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/assign/task", name="assign_task")
     */
    public function assignTask(Request $request, AssignService $assignService, SecurityService $securityService)
    {
        $team = $this->getUser()->getTeams()[0];
        $task = $this->getDoctrine()->getRepository(Task::class)->find($request->get('id'));
        if ($securityService->teamDataCheck($task, $team) === false) {
            return $this->redirectToRoute('tasks');
        }

        $res = $assignService->assignTask($request, $task);
        return $this->redirect($request->headers->get('referer'));
    }
}
