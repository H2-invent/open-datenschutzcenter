<?php

/**
 * Modified by
 * User: Jan Juister
 * Date: 13.05.2022
 */

namespace App\Controller;

use App\Entity\Loeschkonzept;
use App\Form\Type\LoeschkonzeptType;
use App\Service\ApproveService;
use App\Service\CurrentTeamService;
use App\Service\DisableService;
use App\Service\SecurityService;
use App\Service\LoeschkonzeptService;
use App\Repository\LoeschkonzeptRepository;
use App\Repository\VVTDatenkategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;


/**
 * @Route("/loeschkonzept")
 */
class LoeschkonzeptController extends AbstractController
{
    /**
     * @Route("/", name="app_loeschkonzept_index", methods={"GET"})
     */
    public function index(VVTDatenkategorieRepository $vvtDatenkategorieRepository, LoeschkonzeptRepository $loeschkonzeptRepository, SecurityService $securityService, CurrentTeamService $currentTeamService): Response
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $loeschkonzepte = $loeschkonzeptRepository->findByTeam($team);

        # remove all kategories from loeschkonzepte
        foreach ($loeschkonzepte as $loeschkonzept) {
            $datenkategories = $loeschkonzept->getVvtdatenkategories();
            foreach ($datenkategories as $datenkategorie) {
                $loeschkonzept->removeVvtdatenkategory($datenkategorie);
            }
        }
        
        # add datenkategory to loeschkonzept if it is the newest relation
        $vvtDatenkategories = $vvtDatenkategorieRepository->findByTeam($team);
        foreach ($vvtDatenkategories as $vvtDatenkategorie) {
            $loeschkonzept = $vvtDatenkategorie->getLoeschkonzept()->last();
            if ($loeschkonzept != false){
                $loeschkonzept->addVvtdatenkategory($vvtDatenkategorie);
            }
        }
        
        return $this->render('loeschkonzept/index.html.twig', [
            'loeschkonzepte' => $loeschkonzepte,
            'currentTeam' => $team,
        ]);
    }

    /**
     * @Route("/new", name="app_loeschkonzept_new", methods={"GET", "POST"})
     */
    public function new (Request $request, LoeschkonzeptRepository $loeschkonzeptRepository, EntityManagerInterface $entityManager, SecurityService $securityService, LoeschkonzeptService $loeschkonzeptService, CurrentTeamService $currentTeamService): Response
    {
        $user = $this->getUser();
        $team = $currentTeamService->getTeamFromSession($user);
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }
        $loeschkonzept = $loeschkonzeptService->newLoeschkonzept($team, $user);
        $form = $loeschkonzeptService->createForm($loeschkonzept, $team);
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

        return $this->renderForm('loeschkonzept/new.html.twig', [
            'loeschkonzept' => $loeschkonzept,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_loeschkonzept_show", methods={"GET"})
     */
    public function show(Loeschkonzept $loeschkonzept): Response
    {
        return $this->render('loeschkonzept/show.html.twig', [
            'loeschkonzept' => $loeschkonzept,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_loeschkonzept_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Loeschkonzept $loeschkonzept, LoeschkonzeptRepository $loeschkonzeptRepository, EntityManagerInterface $entityManager, VVTDatenkategorieRepository $VvtDatenkategorieRepository, LoeschkonzeptService $loeschkonzeptService, SecurityService $securityService, CurrentTeamService $currentTeamService): Response
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('app_loeschkonzept_index');
        }

        # remove datenkategory from loeschkonzept if it is not the newest relation
        $vvtDatenkategories = $VvtDatenkategorieRepository->findByTeam($team);
        foreach ($vvtDatenkategories as $vvtDatenkategorie) {
            if ($vvtDatenkategorie->getLoeschkonzept()->last() != $loeschkonzept) {
                $loeschkonzept->removeVvtdatenkategory($vvtDatenkategorie);
            }
        }
        
        $newloeschkonzept = $loeschkonzeptService->cloneLoeschkonzept($loeschkonzept);
        foreach ($loeschkonzept->getVvtdatenkategories() as $datenkategorie){
            $newloeschkonzept->addVvtdatenkategory($datenkategorie);
        }
        $form = $loeschkonzeptService->createForm($newloeschkonzept, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $loeschkonzeptRepository->add($newloeschkonzept); 

            $newloeschkonzept->setActiv(true);
            $loeschkonzept->setActiv(false);

            $entityManager->persist($loeschkonzept);
            $entityManager->persist($newloeschkonzept);         

            $entityManager->flush();

            return $this->redirectToRoute('app_loeschkonzept_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('loeschkonzept/edit.html.twig', [
            'loeschkonzept' => $newloeschkonzept,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_loeschkonzept_delete", methods={"POST"})
     */
    public function delete(Request $request, Loeschkonzept $loeschkonzept, LoeschkonzeptRepository $loeschkonzeptRepository, VVTDatenkategorieRepository $VvtDatenkategorieRepository, EntityManagerInterface $entityManager, SecurityService $securityService, DisableService $disableService): Response
    {
        $team = $this->getUser()->getAdminUser();
        if ($securityService->teamCheck($team) === true) 
        {
    
            $loeschkonzept->setActiv(false);
            $entityManager->persist($loeschkonzept);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_loeschkonzept_index', [], Response::HTTP_SEE_OTHER);
    }
}
