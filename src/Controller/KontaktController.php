<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 15.05.2020
 * Time: 09:15
 */

namespace App\Controller;

use App\Entity\Kontakte;
use App\Form\Type\KontaktType;
use App\Repository\KontakteRepository;
use App\Service\ApproveService;
use App\Service\CurrentTeamService;
use App\Service\DisableService;
use App\Service\SecurityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class KontaktController extends AbstractController
{
    #[Route(path: '/kontakt', name: 'kontakt')]
    public function index(SecurityService $securityService,
                          KontakteRepository $contactRepository,
                          CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $contacte = $contactRepository->findBy(array('team' => $team, 'activ' => true));

        return $this->render('kontakt/index.html.twig', [
            'kontakte' => $contacte,
            'title' => 'Kontakte',
            'currentTeam' => $team,
        ]);
    }

    #[Route(path: '/kontakt/neu', name: 'kontakt_new')]
    public function addKontakt(ValidatorInterface $validator,
                               Request $request,
                               EntityManagerInterface $entityManager,
                               SecurityService $securityService,
                               CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $contact = new Kontakte();
        $contact->setTeam($team);
        $contact->setActiv(1);
        $form = $this->createForm(KontaktType::class, $contact);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $errors = $validator->validate($data);
            if (count($errors) == 0) {
                $entityManager->persist($data);
                $entityManager->flush();
                return $this->redirectToRoute('kontakt');
            }
        }
        return $this->render('kontakt/edit.html.twig', [
            'kontakt' => $contact,
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'Kontakt erstellen',
            'new' => true,
        ]);
    }

    #[Route(path: '/kontakt/edit', name: 'kontakt_edit')]
    public function editKontakt(ValidatorInterface $validator,
                                Request $request,
                                EntityManagerInterface $entityManager,
                                KontakteRepository $contactRepository,
                                SecurityService $securityService,
                                CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $contact = $contactRepository->find($request->get('id'));
        if ($securityService->teamDataCheck($contact, $team) === false) {
            return $this->redirectToRoute('kurse');
        }

        $form = $this->createForm(KontaktType::class, $contact);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $errors = $validator->validate($data);
            if (count($errors) == 0) {
                $entityManager->persist($data);
                $entityManager->flush();
                return $this->redirectToRoute('kontakt_edit', ['id' => $contact->getId(), 'snack' => 'Erfolgreich gepeichert']);
            }
        }
        return $this->render('kontakt/edit.html.twig', [
            'form' => $form->createView(),
            'kontakt' => $contact,
            'errors' => $errors,
            'title' => 'Kontakt erstellen',
            'snack' => $request->get('snack')
        ]);
    }

    #[Route(path: '/kontakt/approve', name: 'kontakt_approve')]
    public function approve(Request $request,
                            KontakteRepository $contactRepository,
                            SecurityService $securityService,
                            ApproveService $approveService,
                            CurrentTeamService $currentTeamService)
    {
        $user = $this->getUser();
        $team = $currentTeamService->getTeamFromSession($user);
        $contact = $contactRepository->find($request->get('id'));

        if ($securityService->teamDataCheck($contact, $team) && $securityService->adminCheck($user, $team)) {
            $approve = $approveService->approve($contact, $user);
            return $this->redirectToRoute('kontakt_edit', ['id' => $contact->getId(), 'snack' => $approve['snack']]);
        }

        // if security check fails
        return $this->redirectToRoute('kontakt');
    }

    #[Route(path: '/kontakt/disable', name: 'kontakt_disable')]
    public function disable(Request $request,
                            KontakteRepository $contactRepository,
                            SecurityService $securityService,
                            DisableService $disableService,
                            CurrentTeamService $currentTeamService)
    {
        $user = $this->getUser();
        $team = $currentTeamService->getTeamFromSession($user);
        $contact = $contactRepository->find($request->get('id'));

        if ($securityService->teamDataCheck($contact, $team) && $securityService->adminCheck($user, $team) && !$contact->getApproved()) {
            $disableService->disable($contact, $user);
        }

        return $this->redirectToRoute('kontakt');
    }
}
