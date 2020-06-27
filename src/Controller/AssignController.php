<?php

namespace App\Controller;

use App\Service\AssignService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AssignController extends AbstractController
{
    /**
     * @Route("/assign", name="assign")
     */
    public function index()
    {
        return $this->render('assign/index.html.twig', [
            'controller_name' => 'AssignController',
        ]);
    }

    /**
     * @Route("/assign/vvt", name="assign_vvt")
     */
    public function assignVvt(Request $request, AssignService $assignService)
    {

        $res = $assignService->assignVvt($request);
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/assign/audit", name="assign_audit")
     */
    public function assignAudit(Request $request, AssignService $assignService)
    {

        $res = $assignService->assignAudit($request);
        return $this->redirect($request->headers->get('referer'));
    }
}
