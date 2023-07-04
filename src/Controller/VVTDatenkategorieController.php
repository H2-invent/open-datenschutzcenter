<?php

/**
 * Modified by
 * User: Jan Juister
 * Date: 13.05.2022
 */

namespace App\Controller;

use App\Entity\VVTDatenkategorie;
use App\Form\Type\VVTDatenkategorieType;
use App\Repository\VVTDatenkategorieRepository;
use App\Service\CurrentTeamService;
use App\Service\SecurityService;
use App\Service\VVTDatenkategorieService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/vvtdatenkategorie')]
class VVTDatenkategorieController extends AbstractController
{
    #[Route(path: '/delete/{id}', name: 'app_vvtdatenkategorie_delete', methods: ['POST'])]
    public function delete(
        Request                $request,
        VVTDatenkategorie      $vVTDatenkategorie,
        EntityManagerInterface $entityManager,
        SecurityService        $securityService,
        CurrentTeamService     $currentTeamService,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        if ($securityService->teamCheck($team) === true) {
            if ($this->isCsrfTokenValid('delete' . $vVTDatenkategorie->getId(), $request->request->get('_token'))) {

                $vVTDatenkategorie->setActiv(false);
                $entityManager->persist($vVTDatenkategorie);
                $entityManager->flush();
            }
        }

        return $this->redirectToRoute('app_vvtdatenkategorie_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/edit/{id}', name: 'app_vvtdatenkategorie_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request                  $request,
        VVTDatenkategorie        $vVTDatenkategorie,
        EntityManagerInterface   $entityManager,
        SecurityService          $securityService,
        VVTDatenkategorieService $vVTDatenkategorieService,
        CurrentTeamService       $currentTeamService,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('app_vvtdatenkategorie_index');
        }

        $newVVTDatenkategorie = $vVTDatenkategorieService->cloneVVTDatenkategorie($vVTDatenkategorie);
        $form = $vVTDatenkategorieService->createForm($newVVTDatenkategorie, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $vVTDatenkategorie->setActiv(false);
            $entityManager->persist($vVTDatenkategorie);

            $entityManager->persist($newVVTDatenkategorie);


            foreach ($vVTDatenkategorie->getLoeschkonzept() as $loeschkonzept) {
                $loeschkonzept->addVvtdatenkategory($newVVTDatenkategorie);
                $entityManager->persist($loeschkonzept);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_vvtdatenkategorie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('vvt_datenkategorie/edit.html.twig', [
            'vvtdatenkategorie' => $vVTDatenkategorie,
            'form' => $form,
        ]);
    }

    #[Route(path: '/', name: 'app_vvtdatenkategorie_index', methods: ['GET'])]
    public function index(
        VVTDatenkategorieRepository $vVTDatenkategorieRepository,
        SecurityService             $securityService,
        CurrentTeamService          $currentTeamService,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }
        return $this->render('vvt_datenkategorie/index.html.twig', [
            'vvtdatenkategories' => $vVTDatenkategorieRepository->findByTeam($team),
            'currentTeam' => $team,
        ]);
    }

    #[Route(path: '/new', name: 'app_vvtdatenkategorie_new', methods: ['GET', 'POST'])]
    public function new(
        Request                  $request,
        EntityManagerInterface   $entityManager,
        SecurityService          $securityService,
        VVTDatenkategorieService $vVTDatenkategorieService,
        CurrentTeamService       $currentTeamService,
    ): Response
    {
        $user = $this->getUser();
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }
        $vVTDatenkategorie = $vVTDatenkategorieService->newVVTDatenkategorie($team, $user);
        $form = $this->createForm(VVTDatenkategorieType::class, $vVTDatenkategorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($vVTDatenkategorie);
            $entityManager->flush();

            return $this->redirectToRoute('app_vvtdatenkategorie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('vvt_datenkategorie/new.html.twig', [
            'vvtdatenkategorie' => $vVTDatenkategorie,
            'form' => $form,
        ]);
    }

    #[Route(path: '/show/{id}', name: 'app_vvtdatenkategorie_show', methods: ['GET'])]
    public function show(VVTDatenkategorie $vVTDatenkategorie): Response
    {

        return $this->render('vvt_datenkategorie/show.html.twig', [
            'vvtdatenkategorie' => $vVTDatenkategorie,
        ]);
    }
}
