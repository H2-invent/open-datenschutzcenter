<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 15.05.2020
 * Time: 09:15
 */

namespace App\Controller;

use App\Entity\Vorfall;
use App\Service\SecurityService;
use App\Service\VorfallService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class VorfallController extends AbstractController
{
    /**
     * @Route("/vorfall", name="vorfall")
     */
    public function index(SecurityService $securityService)
    {
        $team = $this->getUser()->getTeam();

        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $vorfall = $this->getDoctrine()->getRepository(Vorfall::class)->findAllByTeam($team);

        return $this->render('vorfall/index.html.twig', [
            'vorfall' => $vorfall,
        ]);
    }

    /**
     * @Route("/vorfall/new", name="vorfall_new")
     */
    public function addVorfall(ValidatorInterface $validator, Request $request, SecurityService $securityService, VorfallService $vorfallService)
    {
        $team = $this->getUser()->getTeam();
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $vorfall = $vorfallService->newVorfall($team, $this->getUser());

        $form = $vorfallService->createForm($vorfall, $team);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $errors = $validator->validate($data);
            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($data);
                $em->flush();
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

    /**
     * @Route("/vorfall/edit", name="vorfall_edit")
     */
    public function EditVorfall(ValidatorInterface $validator, Request $request, SecurityService $securityService, VorfallService $vorfallService)
    {
        $team = $this->getUser()->getTeam();
        $vorgang = $this->getDoctrine()->getRepository(Vorfall::class)->find($request->get('id'));

        if ($securityService->teamDataCheck($vorgang, $team) === false) {
            return $this->redirectToRoute('vorfall');
        }

        $newVorgang = $vorfallService->cloneVorfall($vorgang, $this->getUser());

        $form = $vorfallService->createForm($newVorgang, $team);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {

            // Is Current Version already a historical version.
            if ($vorgang->getActiv() === false) {
                return $this->redirectToRoute('vorfall_edit', array('id' => $vorgang->getId(), 'snack' => 'Version ist nicht mehr aktiv und kann nicht geändert werden.'));
            }

            $vorgang->setActiv(false);
            $newVorgang = $form->getData();
            $errors = $validator->validate($newVorgang);
            if (count($errors) == 0) {

                $em = $this->getDoctrine()->getManager();
                $em->persist($newVorgang);
                $em->persist($vorgang);
                $em->flush();
                return $this->redirectToRoute('vorfall_edit', ['id' => $newVorgang->getId(), 'snack' => 'Erfolgreich gespeichert']);
            }
        }
        return $this->render('vorfall/edit.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'Datenschutzvorfall bearbeiten',
            'vorfall' => $vorgang,
            'activ' => $vorgang->getActiv(),
            'snack' => $request->get('snack')
        ]);
    }
}
