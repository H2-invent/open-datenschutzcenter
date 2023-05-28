<?php

namespace App\Controller;

use App\Repository\TeamRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FirstRunController extends AbstractController
{
    #[Route(path: '/first/run', name: 'first_run')]
    public function index(TeamRepository $teamRepository): Response
    {
        $teams = $teamRepository->findAll();
        if (sizeof($teams) !== 0) {
            return $this->redirectToRoute('dashboard');
        }

        return $this->redirectToRoute('team_create');
    }
}
