<?php

namespace App\Controller;

use App\Entity\Policies;
use App\Service\ApproveService;
use App\Service\AssignService;
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
    /**
     * @Route("/policies", name="policies")
     */
    public function index(SecurityService $securityService)
    {
        $team = $this->getUser()->getTeam();
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }
        $polcies = $this->getDoctrine()->getRepository(Policies::class)->findActivByTeam($team);

        return $this->render('policies/index.html.twig', [
            'data' => $polcies,

        ]);
    }

    /**
     * @Route("/policy/new", name="policy_new")
     */
    public function addPolicy(ValidatorInterface $validator, Request $request, PoliciesService $policiesService, SecurityService $securityService)
    {
        $team = $this->getUser()->getTeam();

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
            'errors' => $errors,
            'title' => 'Richtlinie erstellen',
            'activNummer' => true,
            'vvt' => $policy,
            'activ' => $policy->getActiv(),
            'CTA' => false,
        ]);
    }


    /**
     * @Route("/policy/edit", name="policy_edit")
     */
    public function editPolicy(ValidatorInterface $validator, Request $request, PoliciesService $policiesService, SecurityService $securityService, AssignService $assignService)
    {
        $team = $this->getUser()->getTeam();
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
            'title' => 'Richtlinie bearbeiten',
            'policy' => $policy,
            'activ' => $policy->getActiv(),
            'snack' => $request->get('snack')
        ]);
    }

    /**
     * @Route("/policy/approve", name="policy_approve")
     */
    public function approvePolicy(Request $request, SecurityService $securityService, ApproveService $approveService)
    {
        $team = $this->getUser()->getAdminUser();
        $policy = $this->getDoctrine()->getRepository(Policies::class)->find($request->get('id'));

        if ($securityService->teamDataCheck($policy, $team) === false) {
            return $this->redirectToRoute('policies');
        }
        $approve = $approveService->approve($policy, $this->getUser());

        return $this->redirectToRoute('policy_edit', ['id' => $policy->getId(), 'snack' => $approve['snack']]);
    }

    /**
     * @Route("/policy/download/{id}", name="policy_download_file", methods={"GET"})
     * @ParamConverter("policies", options={"mapping"={"id"="id"}})
     */
    public function downloadArticleReference(FilesystemInterface $policiesFileSystem, Policies $policies, SecurityService $securityService, LoggerInterface $logger)
    {

        $stream = $policiesFileSystem->read($policies->getUpload());

        $team = $this->getUser()->getTeam();
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
