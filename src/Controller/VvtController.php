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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class VvtController extends BaseController
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
                $this->addSuccessMessage($this->translator->trans(id: 'save.successful', domain: 'general'));
                return $this->redirectToRoute(
                    'vvt',
                );
            }
        }

        $this->setBackButton($this->generateUrl('vvt'));

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

        $this->addSuccessMessage($approve['snack']);


        return $this->redirectToRoute('vvt_edit', ['id' => $approve['data']]);

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
        VVTDatenkategorieService $vvtDatenkategorieService,
        CurrentTeamService       $currentTeamService,
        VVTRepository            $vvtRepository
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        $vvt = $vvtRepository->find($request->get('id'));

        if ($securityService->checkTeamAccessToProcess($vvt, $team) === false) {
            $this->addErrorMessage($this->translator->trans(id: 'accessDeniedError', domain: 'base'));
            return $this->redirectToRoute('vvt');
        }
        $newVvt = $VVTService->cloneVvt($vvt, $this->getUser());
        $latestCategories = [];

        foreach ($vvt->getKategorien() as $category) {//hier haben wir die geklonten Kategorien
            $cloneOf = $category->getCloneOf() ?: $category; //wir hängen die neueste gültige Datenkategorie an den VVT clone an.
            $latestCategory = $vvtDatenkategorieService->findLatestKategorie($cloneOf);
            $newVvt->addKategorien($latestCategory);
            $latestCategories[] = $latestCategory;
        }

        $isEditable = $vvt->getTeam() === $team;
        $form = $VVTService->createForm($newVvt, $team, ['disabled' => !$isEditable]);
        $form->remove('nummer');
        $form->handleRequest($request);
        $assign = $assignService->createForm($vvt, $team);

        $errors = array();
        $inheritedEntities = [];

        if (!$isEditable) {
            // This vvt was inherited by ancestor team.
            // In this case we want inherited categories and deletion concepts available by links inside the vvt form.
            $inheritedEntities = $vvtDatenkategorieService->getInheritedEntities($latestCategories);
        }

        if ($form->isSubmitted() && $form->isValid() && $vvt->getActiv() && !$vvt->getApproved() && $isEditable) {
            $vvt->setActiv(false);
            $newVvt = $form->getData();

            $errors = $validator->validate($newVvt);
            if (count($errors) == 0) {
                foreach ($newVvt->getKategorien() as $kategorie) { // wir haben die fiktiven neuesten Kategories
                    $tmp = $vvtDatenkategorieService->createChild($kategorie);//wir klonen die kategorie damit diese revisionssicher ist
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
                $this->addSuccessMessage($this->translator->trans(id: 'save.successful', domain: 'general'));
                return $this->redirectToRoute(
                    'vvt_edit',
                    [
                        'id' => $newVvt->getId(),
                    ],
                );
            }
        }

        $this->setBackButton($this->generateUrl('vvt'));

        return $this->render('vvt/edit.html.twig', [
            'form' => $form->createView(),
            'assignForm' => $assign->createView(),
            'errors' => $errors,
            'title' => $this->translator->trans(id: 'processing.edit', domain: 'vvt'),
            'vvt' => $vvt,
            'activ' => $vvt->getActiv(),
            'activNummer' => false,
            'isEditable' => $isEditable,
            'currentTeam' => $team,
            'inheritedEntities' => $inheritedEntities,
        ]);
    }

    #[Route(path: '/vvt', name: 'vvt')]
    public function index(
        SecurityService    $securityService,
        Request            $request,
        CurrentTeamService $currentTeamService,
        VVTRepository      $vvtRepository
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }
        $vvt = $vvtRepository->findAllByTeam($team);

        return $this->render('vvt/index.html.twig', [
            'vvt' => $vvt,
            'currentTeam' => $team,
        ]);
    }

    #[Route(path: '/vvt/ignore', name: 'vvt_set_ignored')]
    public function setVvtIgnored(
        Request               $request,
        UrlGeneratorInterface $urlGenerator,
        TeamRepository        $teamRepository,
        VVTRepository         $vvtRepository,
    ): RedirectResponse
    {
        $team = $request->get('team');
        $vvt = $request->get('vvt');
        $active = $request->get('set');

        if (is_numeric($team)) {
            $team = $teamRepository->find($team);
        }

        if (is_numeric($vvt)) {
            $vvt = $vvtRepository->find($vvt);
        }

        if($team && $vvt) {
            if ($active) {
                $team->removeIgnoredInheritance($vvt);
            } else {
                $team->addIgnoredInheritance($vvt);
            }
            $this->em->persist($vvt);
            $this->em->persist($team);
            $this->em->flush();
        }

        $referer = $request->headers->get('referer');
        return new RedirectResponse($referer ?: $urlGenerator->generate('dashboard'));
    }
}
