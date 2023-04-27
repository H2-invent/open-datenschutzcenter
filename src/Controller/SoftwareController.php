<?php

namespace App\Controller;

use App\Entity\Software;
use App\Entity\SoftwareConfig;
use App\Repository\SoftwareConfigRepository;
use App\Repository\SoftwareRepository;
use App\Service\ApproveService;
use App\Service\AssignService;
use App\Service\CurrentTeamService;
use App\Service\SecurityService;
use App\Service\SoftwareService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SoftwareController extends AbstractController
{
    #[Route(path: '/software', name: 'software')]
    public function index(SecurityService $securityService,
                          Request $request,
                          SoftwareRepository $softwareRepository,
                          CurrentTeamService $currentTeamService)
    {
        //Request: snack: Snack Notice
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }
        $software = $softwareRepository->findActiveByTeam($team);

        return $this->render('software/index.html.twig', [
            'data' => $software,
            'today' => new \DateTime(),
            'snack' => $request->get('snack'),
            'currentTeam' => $team,
        ]);
    }

    #[Route(path: '/software/new', name: 'software_new')]
    public function addSoftware(ValidatorInterface $validator,
                                Request $request,
                                EntityManagerInterface $entityManager,
                                SoftwareService $softwareService,
                                SecurityService $securityService,
                                CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());

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
                $entityManager->persist($software);
                $entityManager->flush();
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


    #[Route(path: '/software/edit', name: 'software_edit')]
    public function editSoftware(ValidatorInterface $validator,
                                 Request $request,
                                 EntityManagerInterface $entityManager,
                                 SoftwareRepository $softwareRepository,
                                 SoftwareService $softwareService,
                                 SecurityService $securityService,
                                 AssignService $assignService,
                                 CurrentTeamService $currentTeamService)
    {
        //Request: id: SoftwareID, snack:Snack Notice
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $software = $softwareRepository->find($request->get('id'));

        if ($securityService->teamDataCheck($software, $team) === false) {
            return $this->redirectToRoute('software');
        }
        $newSoftware = $softwareService->cloneSoftware($software, $this->getUser());
        $form = $softwareService->createForm($newSoftware, $team);
        $form->handleRequest($request);
        $assign = $assignService->createForm($software, $team);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid() && $software->getActiv() && !$software->getApproved()) {
            $software->setActiv(false);
            $newSoftware = $form->getData();

            $errors = $validator->validate($newSoftware);
            if (count($errors) == 0) {

                foreach ($software->getConfig() as $config) {
                    $newConfig = clone $config;
                    $newConfig->setSoftware($newSoftware);
                    $entityManager->persist($newConfig);
                }
                $entityManager->persist($newSoftware);
                $entityManager->persist($software);
                $entityManager->flush();
                return $this->redirectToRoute('software_edit', ['id' => $newSoftware->getId(), 'snack' => 'Erfolgreich gespeichert']);
            }
        }

        return $this->render('software/edit.html.twig', [
            'form' => $form->createView(),
            'assignForm' => $assign->createView(),
            'errors' => $errors,
            'title' => 'Software bearbeiten',
            'software' => $software,
            'activ' => $software->getActiv(),
            'snack' => $request->get('snack'),
        ]);
    }

    #[Route(path: '/software/config', name: 'software_config_new')]
    public function addConfig(ValidatorInterface $validator,
                              Request $request,
                              EntityManagerInterface $entityManager,
                              SoftwareConfigRepository $softwareConfigRepository,
                              SoftwareRepository $softwareRepository,
                              SoftwareService $softwareService,
                              SecurityService $securityService,
                              CurrentTeamService $currentTeamService)
    {
        //Requests: id: SoftwareID, config: ConfigID
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $software = $softwareRepository->find($request->get('id'));

        if ($securityService->teamDataCheck($software, $team) === false) {
            return $this->redirectToRoute('software');
        }
        $req = $request->get('config');

        if (!$req) {
            $config = $softwareService->newConfig($software);
        } else {
            $config = $softwareConfigRepository->find($req);
        }

        if ($config->getSoftware() !== $software) {
            return $this->redirectToRoute('software', ['snack' => 'FEHLER: Die Konfiguration gehört nicht zu der Software']);

        }

        $form = $softwareService->createConfigForm($config);
        $form->handleRequest($request);


        $errors = array();
        if ($form->isSubmitted() && $form->isValid() && $config->getSoftware()->getActiv() && !$config->getSoftware()->getApproved()) {
            $config = $form->getData();
            $errors = $validator->validate($config);
            if (count($errors) == 0) {
                $config->setCreatedAt(new \DateTime());
                $entityManager->persist($config);
                $entityManager->flush();
                return $this->redirectToRoute('software_edit', ['id' => $software->getId(), 'snack' => 'Erfolgreich gespeichert']);
            }
        }
        return $this->render('software/newConfig.html.twig', [
            'form' => $form->createView(),
            'config' => $config,
            'errors' => $errors,
            'title' => 'Konfiguration für',
            'activ' => $software->getActiv(),
            'software' => $software,
        ]);
    }

    #[Route(path: '/software/config/delete', name: 'software_config_delete')]
    public function deleteConfig(Request $request,
                                 EntityManagerInterface $entityManager,
                                 SoftwareConfigRepository $softwareConfigRepository,
                                 SecurityService $securityService,
                                 CurrentTeamService $currentTeamService)
    {
        // Request: config: ConfigID
        $user = $this->getUser();
        $team = $currentTeamService->getTeamFromSession($user);
        $config = $softwareConfigRepository->find($request->get('config'));

        if ($securityService->teamDataCheck($config->getSoftware(), $team) && $securityService->adminCheck($user, $team)) {
            $entityManager->remove($config);
            $entityManager->flush();
            return $this->redirectToRoute('software_edit', ['id' => $config->getSoftware()->getId(), 'snack' => 'Konfiguration gelöscht']);
        }

        // if security check fails
        return $this->redirectToRoute('software');
    }

    #[Route(path: '/software/approve', name: 'software_approve')]
    public function approveSoftware(Request $request,
                                    EntityManagerInterface $entityManager,
                                    SoftwareRepository $softwareRepository,
                                    SecurityService $securityService,
                                    ApproveService $approveService,
                                    CurrentTeamService $currentTeamService)
    {
        $user = $this->getUser();
        $team = $currentTeamService->getTeamFromSession($user);
        $software = $softwareRepository->find($request->get('id'));

        if ($securityService->teamDataCheck($software, $team) && $securityService->adminCheck($user, $team)) {
            $approve = $approveService->approve($software, $user);

            if ($approve['clone'] === true) {
                $newSoftware = $softwareRepository->find($approve['data']);
                foreach ($software->getConfig() as $config) {
                    $newConfig = clone $config;
                    $newConfig->setSoftware($newSoftware);

                    $entityManager->persist($newConfig);
                }
                $entityManager->persist($newSoftware);
                $entityManager->flush();
            }
            return $this->redirectToRoute('software_edit', ['id' => $approve['data'], 'snack' => $approve['snack']]);
        }

        return $this->redirectToRoute('policies');
    }

    #[Route(path: '/software/config/download/{id}', name: 'software_config_download_file', methods: ['GET'])]
    #[ParamConverter('softwareConfig', options: ['mapping' => ['id' => 'id']])]
    public function downloadArticleReference(SoftwareConfig $softwareConfig,
                                             SecurityService $securityService,
                                             CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $path = $this->getParameter('kernel.project_dir') . "/data/software/" . $softwareConfig->getUpload();

        if ($securityService->teamDataCheck($softwareConfig->getSoftware(), $team) === false) {
            return $this->redirectToRoute('software');
        }

        $file = file_get_contents($path);
        return new Response($file, 200);
    }
}
