<?php

namespace App\Controller;

use App\Entity\Software;
use App\Entity\SoftwareConfig;
use App\Service\AssignService;
use App\Service\SecurityService;
use App\Service\SoftwareService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SoftwareController extends AbstractController
{
    /**
     * @Route("/software", name="software")
     */
    public function index(SecurityService $securityService)
    {
        $team = $this->getUser()->getTeam();
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }
        $software = $this->getDoctrine()->getRepository(Software::class)->findActivByTeam($team);

        return $this->render('software/index.html.twig', [
            'data' => $software,
            'today' => new \DateTime(),

        ]);
    }

    /**
     * @Route("/software/new", name="software_new")
     */
    public function addSoftware(ValidatorInterface $validator, Request $request, SoftwareService $softwareService, SecurityService $securityService)
    {
        $team = $this->getUser()->getTeam();

        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('software');
        }

        $software = $softwareService->newSoftware($team, $this->getUser());
        $form = $softwareService->createForm($software, $team);
        $form->handleRequest($request);


        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $software = $form->getData();
            $errors = $validator->validate($software);
            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($software);
                $em->flush();
                return $this->redirectToRoute('software');
            }
        }
        return $this->render('software/new.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'Software anlegen',
            'activNummer' => true,
            'vvt' => $software,
            'activ' => $software->getActiv(),
        ]);
    }


    /**
     * @Route("/software/edit", name="software_edit")
     */
    public function editSoftware(ValidatorInterface $validator, Request $request, SoftwareService $softwareService, SecurityService $securityService, AssignService $assignService)
    {
        $team = $this->getUser()->getTeam();
        $software = $this->getDoctrine()->getRepository(Software::class)->find($request->get('id'));

        if ($securityService->teamDataCheck($software, $team) === false) {
            return $this->redirectToRoute('software');
        }
        $newSoftware = $softwareService->cloneSoftware($software, $this->getUser());
        $form = $softwareService->createForm($newSoftware, $team);
        $form->handleRequest($request);
        $assign = $assignService->createForm($software, $team);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $software->setActiv(false);
            $newSoftware = $form->getData();

            $errors = $validator->validate($newSoftware);
            if (count($errors) == 0) {

                foreach ($software->getConfig() as $config) {
                    $newConfig = clone $config;
                    $newConfig->setSoftware($newSoftware);
                    $em->persist($newConfig);
                }
                $em->persist($newSoftware);
                $em->persist($software);
                $em->flush();
                return $this->redirectToRoute('software_edit', ['id' => $newSoftware->getId(), 'snack' => 'Erfolgreich gespeichert']);
            }
        }

        return $this->render('software/edit.html.twig', [
            'form' => $form->createView(),
            'assignForm' => $assign->createView(),
            'errors' => $errors,
            'title' => 'Richtlinie bearbeiten',
            'software' => $software,
            'activ' => $software->getActiv(),
            'snack' => $request->get('snack'),
        ]);
    }

    /**
     * @Route("/software/config", name="software_config_new")
     */
    public function addConfig(ValidatorInterface $validator, Request $request, SoftwareService $softwareService, SecurityService $securityService)
    {
        $team = $this->getUser()->getTeam();
        $software = $this->getDoctrine()->getRepository(Software::class)->find($request->get('id'));

        if ($securityService->teamDataCheck($software, $team) === false) {
            return $this->redirectToRoute('software');
        }
        $req = $request->get('config');

        if (!$req) {
            $config = $softwareService->newConfig($software);
        } else {
            $config = $this->getDoctrine()->getRepository(SoftwareConfig::class)->find($req);
        }

        $form = $softwareService->createConfigForm($config);
        $form->handleRequest($request);


        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $config = $form->getData();
            $errors = $validator->validate($config);
            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $config->setCreatedAt(new \DateTime());
                $em->persist($config);
                $em->flush();
                return $this->redirectToRoute('software_edit', ['id' => $software->getId(), 'snack' => 'Erfolgreich gespeichert']);
            }
        }
        return $this->render('software/newConfig.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'Config anlegen für',
            'activ' => true,
            'software' => $software,
        ]);
    }

    /**
     * @Route("/software/config/delete", name="software_config_delete")
     */
    public function deleteConfig(ValidatorInterface $validator, Request $request, SoftwareService $softwareService, SecurityService $securityService)
    {
        $team = $this->getUser()->getAdminUser();
        $software = $this->getDoctrine()->getRepository(Software::class)->find($request->get('id'));

        if ($securityService->teamDataCheck($software, $team) === false) {
            return $this->redirectToRoute('software');
        }

        $config = $this->getDoctrine()->getRepository(SoftwareConfig::class)->find($request->get('config'));
        $em = $this->getDoctrine()->getManager();
        $em->remove($config);
        $em->flush();
        return $this->redirectToRoute('software_edit', ['id' => $software->getId(), 'snack' => 'Konfiguration gelöscht']);
    }
}
