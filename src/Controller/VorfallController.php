<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 15.05.2020
 * Time: 09:15
 */

namespace App\Controller;

use App\Entity\Vorfall;
use App\Repository\VorfallRepository;
use App\Service\ApproveService;
use App\Service\AssignService;
use App\Service\CurrentTeamService;
use App\Service\SecurityService;
use App\Service\VorfallService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class VorfallController extends AbstractController
{
    #[Route(path: '/vorfall', name: 'vorfall')]
    public function index(SecurityService $securityService,
                          VorfallRepository $incidentRepository,
                          CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());

        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $vorfall = $incidentRepository->findAllByTeam($team);

        return $this->render('vorfall/index.html.twig', [
            'vorfall' => $vorfall,
            'currentTeam' => $team,
        ]);
    }

    #[Route(path: '/vorfall/new', name: 'vorfall_new')]
    public function addVorfall(ValidatorInterface $validator,
                               Request $request,
                               EntityManagerInterface $entityManager,
                               SecurityService $securityService,
                               VorfallService $incidentService,
                               CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $vorfall = $incidentService->newVorfall($team, $this->getUser());

        $form = $incidentService->createForm($vorfall, $team);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $errors = $validator->validate($data);
            if (count($errors) == 0) {
                $entityManager->persist($data);
                $entityManager->flush();
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
    public function EditVorfall(ValidatorInterface $validator,
                                Request $request,
                                EntityManagerInterface $entityManager,
                                VorfallRepository $incidentRepository,
                                SecurityService $securityService,
                                VorfallService $incidentService,
                                AssignService $assignService,
                                CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $vorgang = $incidentRepository->find($request->get('id'));

        if ($securityService->teamDataCheck($vorgang, $team) === false) {
            return $this->redirectToRoute('vorfall');
        }

        $newVorgang = $incidentService->cloneVorfall($vorgang, $this->getUser());

        $form = $incidentService->createForm($newVorgang, $team);
        $form->handleRequest($request);
        $assign = $assignService->createForm($vorgang, $team);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid() && $vorgang->getActiv() === true) {

            $vorgang->setActiv(false);
            $newVorgang = $form->getData();
            $errors = $validator->validate($newVorgang);
            if (count($errors) == 0) {
                $entityManager->persist($newVorgang);
                $entityManager->persist($vorgang);
                $entityManager->flush();
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
    public function approve(Request $request,
                            VorfallRepository $incidentRepository,
                            SecurityService $securityService,
                            ApproveService $approveService,
                            CurrentTeamService $currentTeamService)
    {
        $user = $this->getUser();
        $team = $currentTeamService->getTeamFromSession($user);
        $vorfall = $incidentRepository->find($request->get('id'));

        if ($securityService->teamDataCheck($vorfall, $team) && $securityService->adminCheck($user, $team)) {
            $approve = $approveService->approve($vorfall, $user);
            return $this->redirectToRoute('vorfall_edit', ['id' => $approve['data'], 'snack' => $approve['snack']]);
        }

        // if security check fails
        return $this->redirectToRoute('vvt');
    }
}
