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
use App\Service\CurrentTeamService;
use App\Service\DisableService;
use App\Service\SecurityService;
use App\Service\VVTDatenkategorieService;
use App\Service\VVTService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class VvtController extends AbstractController
{
    #[Route(path: '/vvt', name: 'vvt')]
    public function index(SecurityService $securityService, Request $request, CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }
        $vvt = $this->getDoctrine()->getRepository(VVT::class)->findActiveByTeam($team);

        return $this->render('vvt/index.html.twig', [
            'vvt' => $vvt,
            'snack' => $request->get('snack'),
            'currentTeam' => $team,
        ]);
    }

    #[Route(path: '/vvt/new', name: 'vvt_new')]
    public function addVvt(ValidatorInterface $validator, Request $request, VVTService $VVTService, SecurityService $securityService, VVTDatenkategorieService $VVTDatenkategorieService, CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());

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

                foreach ($vvt->getKategorien() as $kategorie){
                    $tmp= $VVTDatenkategorieService->createChild($kategorie);
                    $vvt->removeKategorien($kategorie);
                    $vvt->addKategorien($tmp);
                }

                $em->persist($vvt);
                $em->flush();
                return $this->redirectToRoute('vvt', ['snack' => 'Erfolgreich angelegt']);
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


    #[Route(path: '/vvt/edit', name: 'vvt_edit')]
    public function editVvt(ValidatorInterface $validator, Request $request, VVTService $VVTService, SecurityService $securityService, AssignService $assignService, VVTDatenkategorieService $VVTDatenkategorieService, CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $vvt = $this->getDoctrine()->getRepository(VVT::class)->find($request->get('id'));

        if ($securityService->teamDataCheck($vvt, $team) === false) {
            return $this->redirectToRoute('vvt');
        }
        $newVvt = $VVTService->cloneVvt($vvt, $this->getUser());

        foreach ($vvt->getKategorien() as $cloneKat){//hier haben wir die geklonten KAtegorien
            $newVvt->addKategorien($VVTDatenkategorieService->findLatestKategorie($cloneKat->getCloneOf()));//wir hängen die neueste gültige Datenkategorie an den VVT clone an.
        }

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
                foreach ($newVvt->getKategorien() as $kategorie){ // wir haben die fiktiven neuesten Kategories
                    $tmp= $VVTDatenkategorieService->createChild($kategorie);//wir klonen die kategorie damit diese revisionssicher ist
                    $newVvt->removeKategorien($kategorie);//wir entferenen die fiktive neues kategorie
                    $newVvt->addKategorien($tmp);//wir fügen die geklonte kategorie an
                }
                
                if ($vvt->getActivDsfa()) {
                    $dsfa = $vvt->getActivDsfa();
                    $newDsfa = clone $dsfa;
                    $newDsfa->setVvt($newVvt);
                    $newDsfa->setPrevious(null);
                    $em->persist($newDsfa);
                }

                foreach ($vvt->getPolicies() as $item) {
                    $item->addProcess($newVvt);
                    $em->persist($item);
                }

                foreach ($vvt->getSoftware() as $software) {
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


    #[Route(path: '/vvt/dsfa/new', name: 'vvt_dsfa_new')]
    public function newVvtDsfa(ValidatorInterface $validator, Request $request, VVTService $VVTService, SecurityService $securityService, CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
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

    #[Route(path: '/vvt/dsfa/edit', name: 'vvt_dsfa_edit')]
    public function editVvtDsfa(ValidatorInterface $validator, Request $request, VVTService $VVTService, SecurityService $securityService, AssignService $assignService, CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
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

    #[Route(path: '/vvt/approve', name: 'vvt_approve')]
    public function approveVvt(Request $request, SecurityService $securityService, ApproveService $approveService, CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $vvt = $this->getDoctrine()->getRepository(VVT::class)->find($request->get('id'));

        if ($securityService->teamDataCheck($vvt, $team) === false) {
            return $this->redirectToRoute('vvt');
        }
        $approve = $approveService->approve($vvt, $this->getUser());
        if ($approve['clone'] === true) {
            $newVvt = $this->getDoctrine()->getRepository(VVT::class)->find($approve['data']);

            $em = $this->getDoctrine()->getManager();
            if ($vvt->getActivDsfa()) {
                $dsfa = $vvt->getActivDsfa();
                $newDsfa = clone $dsfa;
                $newDsfa->setVvt($newVvt);
                $newDsfa->setPrevious(null);
                $em->persist($newDsfa);
            }
            foreach ($vvt->getPolicies() as $item) {
                $item->addProcess($newVvt);
                $em->persist($item);
            }
            foreach ($vvt->getSoftware() as $software) {
                $software->addVvt($newVvt);
                $em->persist($software);
            }

            $em->persist($newVvt);
            $em->flush();
        }

        return $this->redirectToRoute('vvt_edit', ['id' => $approve['data'], 'snack' => $approve['snack']]);

    }

    #[Route(path: '/vvt/clone', name: 'vvt_clone')]
    public function cloneVvt(Request $request, SecurityService $securityService, VVTService $VVTService, ValidatorInterface $validator, CurrentTeamService $currentTeamService)
    {
        $vvt = $this->getDoctrine()->getRepository(VVT::class)->find($request->get('id'));
        $team = $currentTeamService->getTeamFromSession($this->getUser());

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

    #[Route(path: '/vvt/disable', name: 'vvt_disable')]
    public function disableVvt(Request $request, SecurityService $securityService, DisableService $disableService, CurrentTeamService $currentTeamService)
    {
        $user = $this->getUser();
        $team = $currentTeamService->getTeamFromSession($user);
        $vvt = $this->getDoctrine()->getRepository(VVT::class)->find($request->get('id'));

        if ($securityService->teamDataCheck($vvt, $team) && $securityService->adminCheck($user, $team) && !$vvt->getApproved()) {
            $disableService->disable($vvt, $user);
        }

        return $this->redirectToRoute('vvt');
    }
}
