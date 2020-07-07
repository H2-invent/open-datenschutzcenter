<?php

namespace App\Controller;

use App\Entity\Policies;
use App\Service\AssignService;
use App\Service\PoliciesService;
use App\Service\SecurityService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
        if ($form->isSubmitted() && $form->isValid()) {
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
}
