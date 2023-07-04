<?php

namespace App\Controller;

use App\Service\CurrentTeamService;
use App\Service\SecurityService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExternalController extends AbstractController
{
    #[Route(path: '/external', name: 'external')]
    public function index(
        SecurityService    $securityService,
        CurrentTeamService $currentTeamService,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('external/index.html.twig', [
            'team' => $team,
            'hash' => md5($team->getName()),
        ]);
    }
}
