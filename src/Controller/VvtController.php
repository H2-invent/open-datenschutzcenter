<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 15.05.2020
 * Time: 09:15
 */

namespace App\Controller;

use App\Entity\VVT;
use App\Entity\VVTDsfa;
use App\Form\Type\VvtDsfaType;
use App\Service\ApproveService;
use App\Service\AssignService;
use App\Service\DisableService;
use App\Service\SecurityService;
use App\Service\VVTService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class VvtController extends AbstractController
{
    /**
     * @Route("/vvt", name="vvt")
     */
    public function index(SecurityService $securityService)
    {
        $team = $this->getUser()->getTeam();
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }
        $vvt = $this->getDoctrine()->getRepository(VVT::class)->findActivByTeam($team);

        return $this->render('vvt/index.html.twig', [
            'vvt' => $vvt,

        ]);
    }

    /**
     * @Route("/vvt/new", name="vvt_new")
     */
    public function addVvt(ValidatorInterface $validator, Request $request, VVTService $VVTService, SecurityService $securityService)
    {
        $team = $this->getUser()->getTeam();

        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('vvt');
        }

        $vvt = $VVTService->newVvt($team, $this->getUser());
        $form = $VVTService->createForm($vvt, $team);
        $form->handleRequest($request);


        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $vvt = $form->getData();
            $errors = $validator->validate($vvt);
            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($vvt);
                $em->flush();
                return $this->redirectToRoute('vvt');
            }
        }
        return $this->render('vvt/new.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'Verarbeitung erfassen',
            'activNummer' => true,
            'vvt' => $vvt,
            'activ' => $vvt->getActiv(),
            'CTA' => false,
        ]);
    }


    /**
     * @Route("/vvt/edit", name="vvt_edit")
     */
    public function editVvt(ValidatorInterface $validator, Request $request, VVTService $VVTService, SecurityService $securityService, AssignService $assignService)
    {
        $team = $this->getUser()->getTeam();
        $vvt = $this->getDoctrine()->getRepository(VVT::class)->find($request->get('id'));

        if ($securityService->teamDataCheck($vvt, $team) === false) {
            return $this->redirectToRoute('vvt');
        }
        $newVvt = $VVTService->cloneVvt($vvt, $this->getUser());
        $form = $VVTService->createForm($newVvt, $team);
        $form->remove('nummer');
        $form->handleRequest($request);
        $assign = $assignService->createForm($vvt, $team);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid() && $vvt->getActiv() && !$vvt->getApproved()) {

            $em = $this->getDoctrine()->getManager();
            $vvt->setActiv(false);
            $newVvt = $form->getData();

            $errors = $validator->validate($newVvt);
            if (count($errors) == 0) {

                if ($vvt->getActivDsfa()) {
                    $dsfa = $vvt->getActivDsfa();
                    $newDsfa = clone $dsfa;
                    $newDsfa->setVvt($newVvt);
                    $newDsfa->setPrevious(null);
                    $em->persist($newDsfa);
                }
                foreach ($newVvt->getPolicies() as $item) {
                    $item->addProcess($newVvt);
                    $em->persist($item);
                }
                foreach ($newVvt->getSoftware() as $software) {
                    $software->addVvt($newVvt);
                    $em->persist($software);
                }

                $em->persist($newVvt);
                $em->persist($vvt);
                $em->flush();
                return $this->redirectToRoute('vvt_edit', ['id' => $newVvt->getId(), 'snack' => 'Erfolgreich gespeichert']);
            }
        }

        return $this->render('vvt/edit.html.twig', [
            'form' => $form->createView(),
            'assignForm' => $assign->createView(),
            'errors' => $errors,
            'title' => 'Verarbeitung bearbeiten',
            'vvt' => $vvt,
            'activ' => $vvt->getActiv(),
            'activNummer' => false,
            'snack' => $request->get('snack')
        ]);
    }


    /**
     * @Route("/vvt/dsfa/new", name="vvt_dsfa_new")
     */
    public function newVvtDsfa(ValidatorInterface $validator, Request $request, VVTService $VVTService, SecurityService $securityService)
    {
        $team = $this->getUser()->getTeam();
        $vvt = $this->getDoctrine()->getRepository(VVT::class)->find($request->get('vvt'));

        if ($securityService->teamDataCheck($vvt, $team) === false) {
            return $this->redirectToRoute('vvt');
        }

        $dsfa = $VVTService->newDsfa($team, $this->getUser(), $vvt);

        $form = $this->createForm(VvtDsfaType::class, $dsfa);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $dsfa = $form->getData();
            $errors = $validator->validate($dsfa);
            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($dsfa);
                $em->flush();
                return $this->redirectToRoute('vvt_edit', ['id' => $dsfa->getVvt()->getId(), 'snack' => 'DSFA angelegt']);
            }
        }
        return $this->render('vvt/editDsfa.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'Datenschutzfolgeabschätzung erstellen',
            'dsfa' => $dsfa,
            'activ' => $dsfa->getActiv(),
        ]);
    }

    /**
     * @Route("/vvt/dsfa/edit", name="vvt_dsfa_edit")
     */
    public function editVvtDsfa(ValidatorInterface $validator, Request $request, VVTService $VVTService, SecurityService $securityService, AssignService $assignService)
    {
        $team = $this->getUser()->getTeam();
        $dsfa = $this->getDoctrine()->getRepository(VVTDsfa::class)->find($request->get('dsfa'));

        if ($securityService->teamDataCheck($dsfa->getVvt(), $team) === false) {
            return $this->redirectToRoute('vvt');
        }

        $newDsfa = $VVTService->cloneDsfa($dsfa, $this->getUser());

        $form = $this->createForm(VvtDsfaType::class, $newDsfa);
        $form->handleRequest($request);
        $assign = $assignService->createForm($dsfa, $team);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid() && $dsfa->getActiv() && !$dsfa->getVvt()->getApproved()) {

            $dsfa->setActiv(false);
            $newDsfa = $form->getData();
            $errors = $validator->validate($newDsfa);
            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($newDsfa);
                $em->persist($dsfa);
                $em->flush();
                return $this->redirectToRoute('vvt_dsfa_edit', ['dsfa' => $newDsfa->getId(), 'snack' => 'Erfolgreich gepeichert']);
            }
        }

        return $this->render('vvt/editDsfa.html.twig', [
            'form' => $form->createView(),
            'assignForm' => $assign->createView(),
            'errors' => $errors,
            'title' => 'Datenschutzfolgeabschätzung bearbeiten',
            'dsfa' => $dsfa,
            'activ' => $dsfa->getActiv(),
            'snack' => $request->get('snack')
        ]);
    }

    /**
     * @Route("/vvt/approve", name="vvt_approve")
     */
    public function approveVvt(Request $request, SecurityService $securityService, ApproveService $approveService)
    {
        $team = $this->getUser()->getAdminUser();
        $vvt = $this->getDoctrine()->getRepository(VVT::class)->find($request->get('id'));

        if ($securityService->teamDataCheck($vvt, $team) === false) {
            return $this->redirectToRoute('vvt');
        }
        $approve = $approveService->approve($vvt, $this->getUser());

        return $this->redirectToRoute('vvt_edit', ['id' => $vvt->getId(), 'snack' => $approve['snack']]);
    }

    /**
     * @Route("/vvt/clone", name="vvt_clone")
     */
    public function cloneVvt(Request $request, SecurityService $securityService, VVTService $VVTService, ValidatorInterface $validator)
    {
        $vvt = $this->getDoctrine()->getRepository(VVT::class)->find($request->get('id'));
        $team = $this->getUser()->getTeam();

        if ($securityService->teamDataCheck($vvt, $team) === false) {
            return $this->redirectToRoute('vvt');
        }

        $newVvt = $VVTService->cloneVvt($vvt, $this->getUser());
        $newVvt->setPrevious(null);
        $newVvt->setApproved(null);
        $newVvt->setApprovedBy(null);

        foreach ($newVvt->getDsfa() as $dsfa) {
            $newVvt->removeDsfa($dsfa);
        }
        foreach ($newVvt->getPolicies() as $policy) {
            $newVvt->removePolicy($policy);
        }

        $form = $VVTService->createForm($newVvt, $team);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $vvt = $form->getData();
            $errors = $validator->validate($vvt);
            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($vvt);
                $em->flush();
                return $this->redirectToRoute('vvt');
            }
        }
        return $this->render('vvt/new.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'Verarbeitung erfassen',
            'activNummer' => true,
            'vvt' => $newVvt,
            'activ' => $newVvt->getActiv(),
            'CTA' => false,
        ]);
    }

    /**
     * @Route("/vvt/disable", name="vvt_disable")
     */
    public function disableVvt(Request $request, SecurityService $securityService, DisableService $disableService)
    {
        $team = $this->getUser()->getAdminUser();
        $vvt = $this->getDoctrine()->getRepository(VVT::class)->find($request->get('id'));

        if ($securityService->teamDataCheck($vvt, $team) === true && !$vvt->getApproved()) {
            $disableService->disable($vvt, $this->getUser());
        }

        return $this->redirectToRoute('vvt');
    }
}
