<?php

namespace App\Controller;

use App\Entity\Team;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DsbController extends AbstractController
{
    #[Route(path: '/ext-dsb', name: 'dsb')]
    public function index()
    {
        $dsbTeams = $this->getUser()->getTeamDsb();
        if (count($dsbTeams) < 1) {
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('dsb/index.html.twig', [
            'teams' => $dsbTeams,
        ]);
    }

    #[Route(path: '/ext-dsb/change', name: 'dsb_change')]
    public function change(Request $request,
                           TeamRepository $teamRepository,
                           EntityManagerInterface $entityManager)
    {
        $team = $teamRepository->find($request->get('id'));

        if (in_array($team, $this->getUser()->getTeamDsb()->toarray())) {
            $user = $this->getUser();
            $user->addTeam($team);
            $team->addAdmin($user);
            $user->setAkademieUser($team);
            $entityManager->persist($user);
            $entityManager->persist($team);
            $entityManager->flush();
            return $this->redirectToRoute('dashboard', ['snack' => 'Team gewächselt']);
        }
        return $this->redirectToRoute('dashboard', ['snack' => 'DSB passt nicht zu diesem Team. Benutzer kann das Team nicht wechseln.']);
    }
}
