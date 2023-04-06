<?php

namespace App\Controller;


use Proxies\__CG__\App\Entity\Vorfall;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HealthCheckController extends AbstractController
{
    #[Route(path: '/health/check', name: 'health_check', methods: ['GET'])]
    public function index(): Response
    {
        try {
            $res = $this->getDoctrine()->getRepository(Vorfall::class)->findAll();
            $vorfall = $res[0]->getFakten();
        } catch (\Exception $exception) {
            throw $this->createNotFoundException('Database not working');
        }

        return new JsonResponse(array('health' => true));
    }
}
