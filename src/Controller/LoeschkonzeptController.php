<?php

/**
 * Modified by
 * User: Jan Juister
 * Date: 13.05.2022
 */

namespace App\Controller;

use App\Entity\Loeschkonzept;
use App\Entity\VVTDatenkategorie;
use App\Form\Type\LoeschkonzeptType;
use App\Repository\LoeschkonzeptRepository;
use App\Repository\VVTDatenkategorieRepository;
use App\Service\CurrentTeamService;
use App\Service\LoeschkonzeptService;
use App\Service\SecurityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/loeschkonzept')]
class LoeschkonzeptController extends AbstractController
{
    #[Route(path: '/{id}/delete', name: 'app_loeschkonzept_delete', methods: ['POST'])]
    public function delete(
        Loeschkonzept          $loeschkonzept,
        EntityManagerInterface $entityManager,
        SecurityService        $securityService,
        CurrentTeamService     $currentTeamService,
    ): Response
    {
        $user = $this->getUser();
        $team = $currentTeamService->getCurrentTeam($user);
        if ($securityService->teamCheck($team) && $securityService->adminCheck($user, $team)) {
            $loeschkonzept->setActiv(false);
            $entityManager->persist($loeschkonzept);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_loeschkonzept_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/{id}/edit', name: 'app_loeschkonzept_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request                     $request,
        Loeschkonzept               $loeschkonzept,
        LoeschkonzeptRepository     $loeschkonzeptRepository,
        EntityManagerInterface      $entityManager,
        VVTDatenkategorieRepository $VvtDatenkategorieRepository,
        LoeschkonzeptService        $loeschkonzeptService,
        SecurityService             $securityService,
        CurrentTeamService          $currentTeamService,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('app_loeschkonzept_index');
        }

        # remove datenkategory from loeschkonzept if it is not the newest relation
        $vvtDatenkategories = $VvtDatenkategorieRepository->findAllByTeam($team);
        foreach ($vvtDatenkategories as $vvtDatenkategorie) {
            if ($vvtDatenkategorie->getLoeschkonzept()->last() != $loeschkonzept) {
                $loeschkonzept->removeVvtdatenkategory($vvtDatenkategorie);
            }
        }

        $newloeschkonzept = $loeschkonzeptService->cloneLoeschkonzept($loeschkonzept);
        foreach ($loeschkonzept->getVvtdatenkategories() as $datenkategorie) {
            $newloeschkonzept->addVvtdatenkategory($datenkategorie);
        }
        $isEditable = $loeschkonzept->getTeam() === $team;
        $form = $loeschkonzeptService->createForm($newloeschkonzept, $team, ['disabled' => !$isEditable]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $isEditable) {

            $loeschkonzeptRepository->add($newloeschkonzept);

            $newloeschkonzept->setActiv(true);
            $loeschkonzept->setActiv(false);

            $entityManager->persist($loeschkonzept);
            $entityManager->persist($newloeschkonzept);

            $entityManager->flush();

            return $this->redirectToRoute('app_loeschkonzept_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('loeschkonzept/edit.html.twig', [
            'loeschkonzept' => $newloeschkonzept,
            'form' => $form,
            'isEditable' => $isEditable,
        ]);
    }

    #[Route(path: '/', name: 'app_loeschkonzept_index', methods: ['GET'])]
    public function index(
        VVTDatenkategorieRepository $vvtDatenkategorieRepository,
        LoeschkonzeptRepository     $loeschkonzeptRepository,
        SecurityService             $securityService,
        CurrentTeamService          $currentTeamService,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $loeschkonzepte = $loeschkonzeptRepository->findLatestByTeam($team);

        return $this->render('loeschkonzept/index.html.twig', [
            'loeschkonzepte' => $loeschkonzepte,
            'currentTeam' => $team,
        ]);
    }

    #[Route(path: '/new', name: 'app_loeschkonzept_new', methods: ['GET', 'POST'])]
    public function new(
        Request                     $request,
        LoeschkonzeptRepository     $loeschkonzeptRepository,
        EntityManagerInterface      $entityManager,
        SecurityService             $securityService,
        LoeschkonzeptService        $loeschkonzeptService,
        CurrentTeamService          $currentTeamService,
        VVTDatenkategorieRepository $categoryRepository,
        FormFactoryInterface        $formBuilder
    ): Response
    {
        $user = $this->getUser();
        $team = $currentTeamService->getCurrentTeam($user);

        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }
        $categories = $categoryRepository->findLatestActiveByTeam($team);
        $options['vvtdatenkategories'] = $categories;

        $loeschkonzept = $loeschkonzeptService->newLoeschkonzept($team, $user);
        $form = $formBuilder->create(LoeschkonzeptType::class, $loeschkonzept, $options);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $loeschkonzeptRepository->add($loeschkonzept);

            foreach ($loeschkonzept->getVvtdatenkategories() as $datenkategory) {
                $datenkategory->addLoeschkonzept($loeschkonzept);

                $entityManager->persist($datenkategory);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_loeschkonzept_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('loeschkonzept/new.html.twig', [
            'loeschkonzept' => $loeschkonzept,
            'form' => $form,
        ]);
    }

    #[Route(path: '/{id}/details', name: 'app_loeschkonzept_show', methods: ['GET'])]
    public function show(Loeschkonzept $loeschkonzept): Response
    {
        return $this->render('loeschkonzept/show.html.twig', [
            'loeschkonzept' => $loeschkonzept,
        ]);
    }
}
