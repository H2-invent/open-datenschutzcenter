<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 15.05.2020
 * Time: 09:15
 */

namespace App\Controller;

use App\Entity\Vorfall;
use App\Service\ApproveService;
use App\Service\AssignService;
use App\Service\CurrentTeamService;
use App\Service\SecurityService;
use App\Service\VorfallService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class VorfallController extends AbstractController
{
    #[Route(path: '/vorfall', name: 'vorfall')]
    public function index(SecurityService $securityService, CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());

        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $vorfall = $this->getDoctrine()->getRepository(Vorfall::class)->findAllByTeam($team);

        return $this->render('vorfall/index.html.twig', [
            'vorfall' => $vorfall,
            'currentTeam' => $team,
        ]);
    }

    #[Route(path: '/vorfall/new', name: 'vorfall_new')]
    public function addVorfall(ValidatorInterface $validator, Request $request, SecurityService $securityService, VorfallService $vorfallService, CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $vorfall = $vorfallService->newVorfall($team, $this->getUser());

        $form = $vorfallService->createForm($vorfall, $team);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $errors = $validator->validate($data);
            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($data);
                $em->flush();
                return $this->redirectToRoute('vorfall');
            }
        }
        return $this->render('vorfall/new.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'Datenschutzvorfall eintragen',
            'vorfall' => $vorfall,
            'activ' => $vorfall->getActiv()
        ]);
    }

    #[Route(path: '/vorfall/edit', name: 'vorfall_edit')]
    public function EditVorfall(ValidatorInterface $validator, Request $request, SecurityService $securityService, VorfallService $vorfallService, AssignService $assignService, CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $vorgang = $this->getDoctrine()->getRepository(Vorfall::class)->find($request->get('id'));

        if ($securityService->teamDataCheck($vorgang, $team) === false) {
            return $this->redirectToRoute('vorfall');
        }

        $newVorgang = $vorfallService->cloneVorfall($vorgang, $this->getUser());

        $form = $vorfallService->createForm($newVorgang, $team);
        $form->handleRequest($request);
        $assign = $assignService->createForm($vorgang, $team);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid() && $vorgang->getActiv() === true) {

            $vorgang->setActiv(false);
            $newVorgang = $form->getData();
            $errors = $validator->validate($newVorgang);
            if (count($errors) == 0) {

                $em = $this->getDoctrine()->getManager();
                $em->persist($newVorgang);
                $em->persist($vorgang);
                $em->flush();
                return $this->redirectToRoute('vorfall_edit', ['id' => $newVorgang->getId(), 'snack' => 'Erfolgreich gespeichert']);
            }
        }
        return $this->render('vorfall/edit.html.twig', [
            'form' => $form->createView(),
            'assignForm' => $assign->createView(),
            'errors' => $errors,
            'title' => 'Datenschutzvorfall bearbeiten',
            'vorfall' => $vorgang,
            'activ' => $vorgang->getActiv(),
            'snack' => $request->get('snack')
        ]);
    }

    #[Route(path: '/vorfall/approve', name: 'vorfall_approve')]
    public function approve(Request $request, SecurityService $securityService, ApproveService $approveService, CurrentTeamService $currentTeamService)
    {
        $user = $this->getUser();
        $team = $currentTeamService->getTeamFromSession($user);
        $vorfall = $this->getDoctrine()->getRepository(Vorfall::class)->find($request->get('id'));

        if ($securityService->teamDataCheck($vorfall, $team) && $securityService->adminCheck($user, $team)) {
            $approve = $approveService->approve($vorfall, $user);
            return $this->redirectToRoute('vorfall_edit', ['id' => $approve['data'], 'snack' => $approve['snack']]);
        }

        // if security check fails
        return $this->redirectToRoute('vvt');
    }
}
