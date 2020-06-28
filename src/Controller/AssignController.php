<?php

namespace App\Controller;

use App\Entity\AuditTom;
use App\Entity\Datenweitergabe;
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

        $assign = $assignService->assign($request, $this->getUser());

        return $this->render('assign/index.html.twig', [
            'assign' => $assign
        ]);
    }

    /**
     * @Route("/assign/vvt", name="assign_vvt")
     */
    public function assignVvt(Request $request, AssignService $assignService, SecurityService $securityService)
    {
        $team = $this->getUser()->getTeam();
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
        $team = $this->getUser()->getTeam();
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
        $team = $this->getUser()->getTeam();
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
        $team = $this->getUser()->getTeam();
        $dsfa = $this->getDoctrine()->getRepository(VVTDsfa::class)->find($request->get('id'));
        if ($securityService->teamDataCheck($dsfa->getVvt(), $team) === false) {
            return $this->redirectToRoute('vvt');
        }

        $res = $assignService->assignDsfa($request, $dsfa);
        return $this->redirect($request->headers->get('referer'));
    }
}
