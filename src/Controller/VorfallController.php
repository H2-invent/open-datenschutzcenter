<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 15.05.2020
 * Time: 09:15
 */

namespace App\Controller;

use App\Entity\Vorfall;
use App\Entity\VVTDatenkategorie;
use App\Entity\VVTPersonen;
use App\Form\Type\VorfallType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class VorfallController extends AbstractController
{
    /**
     * @Route("/vorfall", name="vorfall")
     */
    public function index()
    {
        $vorfall = $this->getDoctrine()->getRepository(Vorfall::class)->findAllByTeam($this->getUser()->getTeam());
        return $this->render('vorfall/index.html.twig', [
            'vorfall' => $vorfall,
        ]);
    }

    /**
     * @Route("/vorfall/new", name="vorfall_new")
     */
    public function addAuditTom(ValidatorInterface $validator, Request $request)
    {
        $team = $this->getUser()->getTeam();
        if ($team === null) {
            return $this->redirectToRoute('dashboard');
        }

        $today = new \DateTime();
        $vorfall = new Vorfall();
        $vorfall->setTeam($team);
        $vorfall->setActiv(true);
        $vorfall->setNummer(uniqid());
        $vorfall->setCreatedAt($today);
        $vorfall->setUser($this->getUser());
        $vorfall->setDatum($today);

        $personen = $this->getDoctrine()->getRepository(VVTPersonen::class)->findAll();
        $kategorien = $this->getDoctrine()->getRepository(VVTDatenkategorie::class)->findAll();


        $form = $this->createForm(VorfallType::class, $vorfall, ['personen'=>$personen,'daten'=>$kategorien]);
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
    public function EditAuditTom(ValidatorInterface $validator, Request $request)
    {
        $team = $this->getUser()->getTeam();
        if ($team === null) {
            return $this->redirectToRoute('dashboard');
        }
        $today = new \DateTime();
        $vorgang = $this->getDoctrine()->getRepository(Vorfall::class)->find($request->get('id'));

        //Sicherheitsfunktion, dass nur eigene und Default TOMs bearbeitet werden können
        if ($vorgang->getTeam() !== $team) {
            return $this->redirectToRoute('vorfall');
        }


        $newVorgang = clone $vorgang;
        $newVorgang->setPrevious($vorgang);
        $newVorgang->setCreatedAt($today);
        $newVorgang->setUser($this->getUser());
        $newVorgang->setTeam($team);

        $personen = $this->getDoctrine()->getRepository(VVTPersonen::class)->findAll();
        $kategorien = $this->getDoctrine()->getRepository(VVTDatenkategorie::class)->findAll();


        $form = $this->createForm(VorfallType::class, $newVorgang, ['personen'=>$personen,'daten'=>$kategorien]);
        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $vorgang->setActiv(false);
            $newVorgang = $form->getData();
            $errors = $validator->validate($newVorgang);
            if (count($errors) == 0) {

                $em = $this->getDoctrine()->getManager();
                $em->persist($newVorgang);
                $em->persist($vorgang);
                $em->flush();
                return $this->redirectToRoute('vorfall');
            }
        }
        return $this->render('vorfall/edit.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'Datenschutzvorfall bearbeiten',
            'vorfall' => $vorgang,
            'activ' => $vorgang->getActiv(),
        ]);
    }
}
