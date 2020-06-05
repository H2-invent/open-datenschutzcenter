<?php

namespace App\Controller;

use App\Entity\AuditTomAbteilung;
use App\Entity\Produkte;
use App\Form\Type\AbteilungType;
use App\Form\Type\ProduktType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProduktController extends AbstractController
{
    /**
     * @Route("/produkt", name="produkt")
     */
    public function index()
    {
        return $this->render('produkt/index.html.twig', [
            'controller_name' => 'ProduktController',
        ]);
    }

    /**
     * @Route("/team_produkte", name="team_produkt")
     */
    public function addProdukte(ValidatorInterface $validator, Request $request)
    {
        $team = $this->getUser()->getAdminUser();

        // Admin Route only
        if ($team === null) {
            return $this->redirectToRoute('dashboard');
        }
        $produkte = $this->getDoctrine()->getRepository(Produkte::class)->findActivByTeam($team);

        if ($request->get('id')) {
            $produkt = $this->getDoctrine()->getRepository(Produkte::class)->find($request->get('id'));
            if ($produkt->getTeam() !== $team) {
                return $this->redirectToRoute('team_produkt');
            }
        } else {
            $produkt = new Produkte();
            $produkt->setActiv(true);
            $produkt->setTeam($team);
        }
        $form = $this->createForm(ProduktType::class, $produkt);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $errors = $validator->validate($data);
            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($data);
                $em->flush();
                return $this->redirectToRoute('team_produkt');
            }
        }
        return $this->render('team/produkt.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'data' => $produkte,
        ]);
    }

    /**
     * @Route("/team_produkt/deaktivieren", name="team_produkt_deativate")
     */
    public function addProduktDeactivate(Request $request)
    {
        $team = $this->getUser()->getAdminUser();

        // Admin Route only
        if ($team === null) {
            return $this->redirectToRoute('dashboard');
        }

        $abteilung = $this->getDoctrine()->getRepository(AuditTomAbteilung::class)->findOneBy(array('id' => $request->get('id')));
        if ($abteilung->getTeam() == $this->getUser()->getTeam()) {
            $abteilung->setActiv(false);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($abteilung);
        $em->flush();
        return $this->redirectToRoute('team_produkt');
    }
}
