<?php

namespace App\Controller;

use App\Entity\Policies;
use App\Service\ApproveService;
use App\Service\AssignService;
use App\Service\CurrentTeamService;
use App\Service\DisableService;
use App\Service\PoliciesService;
use App\Service\SecurityService;
use League\Flysystem\FilesystemInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PoliciesController extends AbstractController
{
    #[Route(path: '/policies', name: 'policies')]
    public function index(SecurityService $securityService, CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }
        $polcies = $this->getDoctrine()->getRepository(Policies::class)->findActiveByTeam($team);

        return $this->render('policies/index.html.twig', [
            'data' => $polcies,
            'currentTeam' => $team,
        ]);
    }

    #[Route(path: '/policy/new', name: 'policy_new')]
    public function addPolicy(ValidatorInterface $validator, Request $request, PoliciesService $policiesService, SecurityService $securityService, CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());

        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('policies');
        }

        $policy = $policiesService->newPolicy($team, $this->getUser());
        $form = $policiesService->createForm($policy, $team);
        $form->handleRequest($request);


        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $policy = $form->getData();
            $errors = $validator->validate($policy);
            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($policy);
                $em->flush();
                return $this->redirectToRoute('policies');
            }
        }
        return $this->render('policies/new.html.twig', [
            'form' => $form->createView(),
            'policy' => $policy,
            'errors' => $errors,
            'title' => 'Richtlinie & Arbeitsanweisung erstellen',
            'activNummer' => true,
            'vvt' => $policy,
            'activ' => $policy->getActiv(),
            'CTA' => false,
        ]);
    }


    #[Route(path: '/policy/edit', name: 'policy_edit')]
    public function editPolicy(ValidatorInterface $validator, Request $request, PoliciesService $policiesService, SecurityService $securityService, AssignService $assignService, CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $policy = $this->getDoctrine()->getRepository(Policies::class)->find($request->get('id'));

        if ($securityService->teamDataCheck($policy, $team) === false) {
            return $this->redirectToRoute('policies');
        }
        $newPolicy = $policiesService->clonePolicy($policy, $this->getUser());
        $form = $policiesService->createForm($newPolicy, $team);
        $form->handleRequest($request);
        $assign = $assignService->createForm($policy, $team);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid() && $policy->getActiv() && !$policy->getApproved()) {

            $em = $this->getDoctrine()->getManager();
            $policy->setActiv(false);
            $newPolicy = $form->getData();
            $errors = $validator->validate($newPolicy);
            if (count($errors) == 0) {
                $em->persist($newPolicy);
                $em->persist($policy);
                $em->flush();
                return $this->redirectToRoute('policy_edit', ['id' => $newPolicy->getId(), 'snack' => 'Erfolgreich gespeichert']);
            }
        }

        return $this->render('policies/edit.html.twig', [
            'form' => $form->createView(),
            'assignForm' => $assign->createView(),
            'errors' => $errors,
            'title' => 'Richtlinie / Arbeitsanweisung bearbeiten',
            'policy' => $policy,
            'activ' => $policy->getActiv(),
            'snack' => $request->get('snack')
        ]);
    }

    #[Route(path: '/policy/approve', name: 'policy_approve')]
    public function approvePolicy(Request $request, SecurityService $securityService, ApproveService $approveService, CurrentTeamService $currentTeamService)
    {
        $user = $this->getUser();
        $team = $currentTeamService->getTeamFromSession($user);
        $policy = $this->getDoctrine()->getRepository(Policies::class)->find($request->get('id'));

        if ($securityService->teamDataCheck($policy, $team) && $securityService->adminCheck($user, $team)) {
            $approve = $approveService->approve($policy, $user);
            return $this->redirectToRoute('policy_edit', ['id' => $approve['data'], 'snack' => $approve['snack']]);
        }

        // if security check fails
        return $this->redirectToRoute('policies');
    }

    #[Route(path: '/policy/disable', name: 'policy_disable')]
    public function disable(Request $request, SecurityService $securityService, DisableService $disableService, CurrentTeamService $currentTeamService)
    {
        $user = $this->getUser();
        $team = $currentTeamService->getTeamFromSession($user);
        $policy = $this->getDoctrine()->getRepository(Policies::class)->find($request->get('id'));

        if ($securityService->teamDataCheck($policy, $team) && $securityService->adminCheck($user, $team) && !$policy->getApproved()) {
            $disableService->disable($policy, $this->getUser());
        }

        return $this->redirectToRoute('policies');
    }

    #[Route(path: '/policy/download/{id}', name: 'policy_download_file', methods: ['GET'])]
    #[ParamConverter('policies', options: ['mapping' => ['id' => 'id']])]
    public function downloadArticleReference(FilesystemInterface $policiesFileSystem, Policies $policies, SecurityService $securityService, LoggerInterface $logger, CurrentTeamService $currentTeamService)
    {

        $stream = $policiesFileSystem->read($policies->getUpload());

        $team = $currentTeamService->getTeamFromSession($this->getUser());
        if ($securityService->teamDataCheck($policies, $team) === false) {
            $message = ['typ' => 'DOWNLOAD', 'error' => true, 'hinweis' => 'Fehlerhafter download. User nicht berechtigt!', 'dokument' => $policies->getUpload(), 'user' => $this->getUser()->getUsername()];
            $logger->error($message['typ'], $message);
            return $this->redirectToRoute('dashboard');
        }

        $type = $policiesFileSystem->getMimetype($policies->getUpload());
        $response = new Response($stream);
        $response->headers->set('Content-Type', $type);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $policies->getUpload())
        );

        $response->headers->set('Content-Disposition', $disposition);
        $message = ['typ' => 'DOWNLOAD', 'error' => false, 'hinweis' => 'Download erfolgreich', 'dokument' => $policies->getUpload(), 'user' => $this->getUser()->getUsername()];
        $logger->info($message['typ'], $message);
        return $response;
    }
}
