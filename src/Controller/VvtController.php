<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 15.05.2020
 * Time: 09:15
 */

namespace App\Controller;

use App\Form\Type\VvtDsfaType;
use App\Repository\TeamRepository;
use App\Repository\VVTDsfaRepository;
use App\Repository\VVTRepository;
use App\Service\ApproveService;
use App\Service\AssignService;
use App\Service\CurrentTeamService;
use App\Service\DisableService;
use App\Service\SecurityService;
use App\Service\VVTDatenkategorieService;
use App\Service\VVTService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class VvtController extends AbstractController
{


    public function __construct(private readonly TranslatorInterface $translator,
                                private EntityManagerInterface       $em,
    )
    {
    }

    #[Route(path: '/vvt/new', name: 'vvt_new')]
    public function addVvt(
        ValidatorInterface       $validator,
        Request                  $request,
        VVTService               $VVTService,
        SecurityService          $securityService,
        VVTDatenkategorieService $VVTDatenkategorieService,
        CurrentTeamService       $currentTeamService,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());

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
                foreach ($vvt->getKategorien() as $kategorie) {
                    $tmp = $VVTDatenkategorieService->createChild($kategorie);
                    $vvt->removeKategorien($kategorie);
                    $vvt->addKategorien($tmp);
                }

                $this->em->persist($vvt);
                $this->em->flush();
                return $this->redirectToRoute(
                    'vvt',
                    [
                        'snack' => $this->translator->trans(id: 'save.successful', domain: 'general'),
                    ],
                );
            }
        }
        return $this->render('vvt/new.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => $this->translator->trans(id: 'processing.track', domain: 'vvt'),
            'activNummer' => true,
            'vvt' => $vvt,
            'activ' => $vvt->getActiv(),
            'CTA' => false,
            'isEditable' => true,
        ]);
    }

    #[Route(path: '/vvt/approve', name: 'vvt_approve')]
    public function approveVvt(
        Request            $request,
        SecurityService    $securityService,
        ApproveService     $approveService,
        CurrentTeamService $currentTeamService,
        VVTRepository      $vvtRepository,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        $vvt = $vvtRepository->find($request->get('id'));

        if ($securityService->teamDataCheck($vvt, $team) === false) {
            return $this->redirectToRoute('vvt');
        }
        $approve = $approveService->approve($vvt, $this->getUser());
        if ($approve['clone'] === true) {
            $newVvt = $vvtRepository->find($approve['data']);

            if ($vvt->getActivDsfa()) {
                $dsfa = $vvt->getActivDsfa();
                $newDsfa = clone $dsfa;
                $newDsfa->setVvt($newVvt);
                $newDsfa->setPrevious(null);
                $this->em->persist($newDsfa);
            }
            foreach ($vvt->getPolicies() as $item) {
                $item->addProcess($newVvt);
                $this->em->persist($item);
            }
            foreach ($vvt->getSoftware() as $software) {
                $software->addVvt($newVvt);
                $this->em->persist($software);
            }

            $this->em->persist($newVvt);
            $this->em->flush();
        }

        return $this->redirectToRoute('vvt_edit', ['id' => $approve['data'], 'snack' => $approve['snack']]);

    }

    #[Route(path: '/vvt/clone', name: 'vvt_clone')]
    public function cloneVvt(
        Request            $request,
        SecurityService    $securityService,
        VVTService         $VVTService,
        ValidatorInterface $validator,
        CurrentTeamService $currentTeamService,
        VVTRepository      $vvtRepository,
    ): Response
    {
        $vvt = $vvtRepository->find($request->get('id'));
        $team = $currentTeamService->getCurrentTeam($this->getUser());

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
                $this->em->persist($vvt);
                $this->em->flush();

                return $this->redirectToRoute('vvt');
            }
        }
        return $this->render('vvt/new.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => $this->translator->trans(id: 'processing.track', domain: 'vvt'),
            'activNummer' => true,
            'vvt' => $newVvt,
            'activ' => $newVvt->getActiv(),
            'CTA' => false,
            'isEditable' => true,
        ]);
    }

    #[Route(path: '/vvt/disable', name: 'vvt_disable')]
    public function disableVvt(
        Request            $request,
        SecurityService    $securityService,
        DisableService     $disableService,
        CurrentTeamService $currentTeamService,
        VVTRepository      $vvtRepository,
    ): Response
    {
        $user = $this->getUser();
        $team = $currentTeamService->getCurrentTeam($user);
        $vvt = $vvtRepository->find($request->get('id'));

        if ($securityService->teamDataCheck($vvt, $team) && $securityService->adminCheck($user, $team) && !$vvt->getApproved()) {
            $disableService->disable($vvt, $user);
        }

        return $this->redirectToRoute('vvt');
    }

    #[Route(path: '/vvt/edit', name: 'vvt_edit')]
    public function editVvt(
        ValidatorInterface       $validator,
        Request                  $request,
        VVTService               $VVTService,
        SecurityService          $securityService,
        AssignService            $assignService,
        VVTDatenkategorieService $VVTDatenkategorieService,
        CurrentTeamService       $currentTeamService,
        VVTRepository            $vvtRepository,
        TeamRepository           $teamRepository
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        $vvt = $vvtRepository->find($request->get('id'));
        $teamPath = $team ? $teamRepository->getPath($team) : null;

        if ($securityService->teamPathDataCheck($vvt, $teamPath) === false) {
            return $this->redirectToRoute('vvt');
        }
        $newVvt = $VVTService->cloneVvt($vvt, $this->getUser());

        foreach ($vvt->getKategorien() as $cloneKat) {//hier haben wir die geklonten KAtegorien
            $newVvt->addKategorien($VVTDatenkategorieService->findLatestKategorie($cloneKat->getCloneOf()));//wir hängen die neueste gültige Datenkategorie an den VVT clone an.
        }

        $isEditable = $vvt->getTeam() === $team;
        $form = $VVTService->createForm($newVvt, $team, ['disabled' => !$isEditable]);
        $form->remove('nummer');
        $form->handleRequest($request);
        $assign = $assignService->createForm($vvt, $team);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid() && $vvt->getActiv() && !$vvt->getApproved() && $isEditable) {
            $vvt->setActiv(false);
            $newVvt = $form->getData();

            $errors = $validator->validate($newVvt);
            if (count($errors) == 0) {
                foreach ($newVvt->getKategorien() as $kategorie) { // wir haben die fiktiven neuesten Kategories
                    $tmp = $VVTDatenkategorieService->createChild($kategorie);//wir klonen die kategorie damit diese revisionssicher ist
                    $newVvt->removeKategorien($kategorie);//wir entferenen die fiktive neues kategorie
                    $newVvt->addKategorien($tmp);//wir fügen die geklonte kategorie an
                }

                if ($vvt->getActivDsfa()) {
                    $dsfa = $vvt->getActivDsfa();
                    $newDsfa = clone $dsfa;
                    $newDsfa->setVvt($newVvt);
                    $newDsfa->setPrevious(null);
                    $this->em->persist($newDsfa);
                }

                foreach ($vvt->getPolicies() as $item) {
                    $item->addProcess($newVvt);
                    $this->em->persist($item);
                }

                foreach ($vvt->getSoftware() as $software) {
                    $software->addVvt($newVvt);
                    $this->em->persist($software);
                }

                $this->em->persist($newVvt);
                $this->em->persist($vvt);
                $this->em->flush();
                return $this->redirectToRoute(
                    'vvt_edit',
                    [
                        'id' => $newVvt->getId(),
                        'snack' => $this->translator->trans(id: 'save.successful', domain: 'general'),
                    ],
                );
            }
        }

        return $this->render('vvt/edit.html.twig', [
            'form' => $form->createView(),
            'assignForm' => $assign->createView(),
            'errors' => $errors,
            'title' => $this->translator->trans(id: 'processing.edit', domain: 'vvt'),
            'vvt' => $vvt,
            'activ' => $vvt->getActiv(),
            'activNummer' => false,
            'snack' => $request->get('snack'),
            'isEditable' => $isEditable,
        ]);
    }

    #[Route(path: '/vvt/dsfa/edit', name: 'vvt_dsfa_edit')]
    public function editVvtDsfa(
        ValidatorInterface $validator,
        Request            $request,
        VVTService         $VVTService,
        SecurityService    $securityService,
        AssignService      $assignService,
        CurrentTeamService $currentTeamService,
        VVTDsfaRepository  $vvtDsfaRepository,
        TeamRepository     $teamRepository
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        $dsfa = $vvtDsfaRepository->find($request->get('dsfa'));
        $teamPath = $team ? $teamRepository->getPath($team) : null;

        if ($securityService->teamPathDataCheck($dsfa->getVvt(), $teamPath) === false) {
            return $this->redirectToRoute('vvt');
        }

        $newDsfa = $VVTService->cloneDsfa($dsfa, $this->getUser());
        $isEditable = $dsfa->getVvt()->getTeam() === $team;

        $form = $this->createForm(VvtDsfaType::class, $newDsfa, ['disabled' => !$isEditable]);
        $form->handleRequest($request);
        $assign = $assignService->createForm($dsfa, $team, ['disabled' => !$isEditable]);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid() && $dsfa->getActiv() && !$dsfa->getVvt()->getApproved() && $isEditable) {

            $dsfa->setActiv(false);
            $newDsfa = $form->getData();
            $errors = $validator->validate($newDsfa);
            if (count($errors) == 0) {
                $this->em->persist($newDsfa);
                $this->em->persist($dsfa);
                $this->em->flush();

                return $this->redirectToRoute(
                    'vvt_dsfa_edit',
                    [
                        'dsfa' => $newDsfa->getId(),
                        'snack' => $this->translator->trans(id: 'save.successful', domain: 'general'),
                    ],
                );
            }
        }

        return $this->render('vvt/editDsfa.html.twig', [
            'form' => $form->createView(),
            'assignForm' => $assign->createView(),
            'errors' => $errors,
            'title' => $this->translator->trans(id: 'dataPrivacyFollowUpEstimation.edit', domain: 'vvt'),
            'dsfa' => $dsfa,
            'activ' => $dsfa->getActiv(),
            'snack' => $request->get('snack'),
            'isEditable' => $isEditable,
        ]);
    }

    #[Route(path: '/vvt', name: 'vvt')]
    public function index(
        SecurityService    $securityService,
        Request            $request,
        CurrentTeamService $currentTeamService,
        VVTRepository      $vvtRepository,
        TeamRepository     $teamRepository
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }
        $teamPath = $teamRepository->getPath($team);
        $vvt = $vvtRepository->findActiveByTeamPath($teamPath);

        return $this->render('vvt/index.html.twig', [
            'vvt' => $vvt,
            'snack' => $request->get('snack'),
            'currentTeam' => $team,
        ]);
    }

    #[Route(path: '/vvt/dsfa/new', name: 'vvt_dsfa_new')]
    public function newVvtDsfa(
        ValidatorInterface $validator,
        Request            $request,
        VVTService         $VVTService,
        SecurityService    $securityService,
        CurrentTeamService $currentTeamService,
        VVTRepository      $vvtRepository,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        $vvt = $vvtRepository->find($request->get('vvt'));

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
                $this->em->persist($dsfa);
                $this->em->flush();
                return $this->redirectToRoute(
                    'vvt_edit',
                    [
                        'id' => $dsfa->getVvt()->getId(),
                        'snack' => $this->translator->trans(id: 'dsfa.created', domain: 'vvt'),
                    ],
                );
            }
        }
        return $this->render('vvt/editDsfa.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => $this->translator->trans(id: 'dataPrivacyFollowUpEstimation.create', domain: 'vvt'),
            'dsfa' => $dsfa,
            'activ' => $dsfa->getActiv(),
            'isEditable' => true,
        ]);
    }
}
