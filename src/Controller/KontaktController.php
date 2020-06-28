<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 15.05.2020
 * Time: 09:15
 */

namespace App\Controller;

use App\Entity\Kontakte;
use App\Form\Type\KontaktType;
use App\Service\SecurityService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class KontaktController extends AbstractController
{
    /**
     * @Route("/kontakt", name="kontakt")
     */
    public function index(SecurityService $securityService)
    {
        $team = $this->getUser()->getTeam();
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $kontakte = $this->getDoctrine()->getRepository(Kontakte::class)->findBy(array('team' => $team, 'activ' => true));

        return $this->render('kontakt/index.html.twig', [
            'kontakte' => $kontakte,
            'title' => 'Kontakte'
        ]);
    }

    /**
     * @Route("/kontakt/neu", name="kontakt_new")
     */
    public function addKontakt(ValidatorInterface $validator, Request $request, SecurityService $securityService)
    {
        $team = $this->getUser()->getTeam();
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $kontakt = new Kontakte();
        $kontakt->setTeam($team);
        $kontakt->setActiv(true);
        $form = $this->createForm(KontaktType::class, $kontakt);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $errors = $validator->validate($data);
            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($data);
                $em->flush();
                return $this->redirectToRoute('kontakt');
            }
        }
        return $this->render('kontakt/edit.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'Kontakt erstellen',
        ]);
    }

    /**
     * @Route("/kontakt/edit", name="kontakt_edit")
     */
    public function editKontakt(ValidatorInterface $validator, Request $request, SecurityService $securityService)
    {
        $team = $this->getUser()->getTeam();
        $kontakt = $this->getDoctrine()->getRepository(Kontakte::class)->find($request->get('id'));
        if ($securityService->teamDataCheck($kontakt, $team) === false) {
            return $this->redirectToRoute('kurse');
        }

        $form = $this->createForm(KontaktType::class, $kontakt);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $errors = $validator->validate($data);
            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($data);
                $em->flush();
                return $this->redirectToRoute('kontakt_edit', ['id' => $kontakt->getId(), 'snack' => 'Erfolgreich gepeichert']);
            }
        }
        return $this->render('kontakt/edit.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'Kontakt erstellen',
            'snack' => $request->get('snack')
        ]);
    }
}
