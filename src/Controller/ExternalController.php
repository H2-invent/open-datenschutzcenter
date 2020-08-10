<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ExternalController extends AbstractController
{
    /**
     * @Route("/external", name="external")
     */
    public function index()
    {
        return $this->render('external/index.html.twig', [
            'team' => $this->getUser()->getTeam(),
        ]);
    }

    /**
     * @Route("/external/video", name="external_video")
     */
    public function video()
    {
        return $this->render('external/video.html.twig', [
            'team' => $this->getUser()->getTeam(),
            'hash' => hash('md5', $this->getUser()->getTeam()->getName()),
        ]);
    }

    /**
     * @Route("/external/doc", name="external_doc")
     */
    public function doc()
    {
        return $this->render('external/doc.html.twig', [
            'team' => $this->getUser()->getTeam(),
        ]);
    }
}
