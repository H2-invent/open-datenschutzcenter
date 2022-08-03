<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\Type\TeamType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FirstRunController extends AbstractController
{
    /**
     * @Route("/first/run", name="first_run")
     */
    public function index(ValidatorInterface $validator, Request $request): Response
    {
        $teams = $this->getDoctrine()->getRepository(Team::class)->findAll();
        if (sizeof($teams) !== 0){
            return $this->redirectToRoute('dashboard');
        }

        $nteam = new Team();
        $nteam->setActiv(true);
        $form = $this->createForm(TeamType::class, $nteam);
        $form->remove('video');
        $form->remove('externalLink');
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $nTeam = $form->getData();
            $errors = $validator->validate($nTeam);
            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $user = $this->getUser();
                $user->addTeam($nteam);
                $nteam->addAdmin($user);
                $em->persist($nTeam);
                $em->persist($user);
                $em->flush();
                return $this->redirectToRoute('dashboard');
            }
        }

        return $this->render('team/index.html.twig', [
            'controller_name' => 'TeamController',
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'Jetzt ein Team anlegen'
        ]);
    }
}
