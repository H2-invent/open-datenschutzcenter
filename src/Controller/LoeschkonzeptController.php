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
    public function index(LoeschkonzeptRepository $loeschkonzeptRepository, SecurityService $securityService): Response
    {
        $team = $this->getUser()->getTeam();
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }
        return $this->render('loeschkonzept/index.html.twig', [
            'loeschkonzepts' => $loeschkonzeptRepository->findByTeam($team),
        ]);
    }

    /**
     * @Route("/new", name="app_loeschkonzept_new", methods={"GET", "POST"})
     */
    public function new (Request $request, LoeschkonzeptRepository $loeschkonzeptRepository, EntityManagerInterface $entityManager, SecurityService $securityService, LoeschkonzeptService $loeschkonzeptService): Response
    {
        $team = $this->getUser()->getTeam();
        $user = $this->getUser();
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }
        $loeschkonzept = new Loeschkonzept();
        $loeschkonzept->setTeam($team);
        $loeschkonzept->setUser($user);
        $loeschkonzept->setActiv(true);
        $loeschkonzept->setCreateAt(new \DateTimeImmutable());
        $form = $loeschkonzeptService->createForm($loeschkonzept, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $loeschkonzeptRepository->add($loeschkonzept);

            foreach ($loeschkonzept->getVvtdatenkategories() as $datenkategory) {
                $datenkategory->setLoeschkonzept($loeschkonzept);
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
    public function edit(Request $request, Loeschkonzept $loeschkonzept, LoeschkonzeptRepository $loeschkonzeptRepository, EntityManagerInterface $entityManager, VVTDatenkategorieRepository $VvtDatenkategorieRepository, LoeschkonzeptService $loeschkonzeptService, SecurityService $securityService, Loeschkonzeptservice $LoeschkonzeptService): Response
    {
        $team = $this->getUser()->getTeam();
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('app_loeschkonzept_index');
        }
        $newloeschkonzept = $LoeschkonzeptService->cloneLoeschkonzept($loeschkonzept);
        $form = $loeschkonzeptService->createForm($newloeschkonzept, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $loeschkonzeptRepository->add($newloeschkonzept);

            $datenkategories = $VvtDatenkategorieRepository->findBy(array('team'=>$team));
            foreach ($datenkategories as $datenkategory) {
                if ($datenkategory->getLoeschkonzept() == $newloeschkonzept) {
                    $datenkategory->setLoeschkonzept(null);
                    $entityManager->persist($datenkategory);
                }
            }

            foreach ($newloeschkonzept->getVvtdatenkategories() as $datenkategory) {
                $datenkategory->setLoeschkonzept($newloeschkonzept);
                $entityManager->persist($datenkategory);
            }          

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
            $datenkategories = $VvtDatenkategorieRepository->findBy(array('team'=>$team));
            foreach ($datenkategories as $datenkategory) {
                if ($datenkategory->getLoeschkonzept() == $loeschkonzept) {
                    $datenkategory->setLoeschkonzept(null);
                    $entityManager->persist($datenkategory);
                }
            }
             
            $loeschkonzept->setActiv(false);
            $entityManager->persist($loeschkonzept);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_loeschkonzept_index', [], Response::HTTP_SEE_OTHER);
    }
}
