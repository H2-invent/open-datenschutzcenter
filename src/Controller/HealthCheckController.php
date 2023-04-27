<?php

namespace App\Controller;


use App\Repository\VorfallRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HealthCheckController extends AbstractController
{
    #[Route(path: '/health/check', name: 'health_check', methods: ['GET'])]
    public function index(VorfallRepository $incidentRepository): Response
    {
        try {
            $res = $incidentRepository->findAll();
            $res[0]->getFakten();
        } catch (\Exception $exception) {
            throw $this->createNotFoundException('Database not working');
        }

        return new JsonResponse(array('health' => true));
    }
}
