<?php

namespace App\Controller;

use App\Service\SecurityService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ExternalController extends AbstractController
{
    /**
     * @Route("/external", name="external")
     */
    public function index(SecurityService $securityService)
    {
        $team = $this->getUser()->getTeam();
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('external/index.html.twig', [
            'team' => $team,
            'hash' => hash('md5', $team->getName()),
        ]);
    }
}
