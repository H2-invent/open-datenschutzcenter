<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 15.05.2020
 * Time: 09:15
 */

namespace App\Controller;

use App\Entity\Datenweitergabe;
use App\Service\DatenweitergabeService;
use App\Service\SecurityService;
use League\Flysystem\FilesystemInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DatenweitergabeController extends AbstractController
{
    /**
     * @Route("/datenweitergabe", name="datenweitergabe")
     */
    public function indexDatenweitergabe(SecurityService $securityService)
    {
        $team = $this->getUser()->getTeam();
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $daten = $this->getDoctrine()->getRepository(Datenweitergabe::class)->findBy(array('team' => $this->getUser()->getTeam(), 'activ' => true, 'art' => 1));
        return $this->render('datenweitergabe/index.html.twig', [
            'table' => $daten,
            'titel' => 'Alle Datenweitergaben nach DSGVO Art. 30(1)',
        ]);
    }

    /**
     * @Route("/auftragsverarbeitung", name="auftragsverarbeitung")
     */
    public function indexAuftragsverarbeitung(SecurityService $securityService)
    {
        $team = $this->getUser()->getTeam();
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $daten = $this->getDoctrine()->getRepository(Datenweitergabe::class)->findBy(array('team' => $this->getUser()->getTeam(), 'activ' => true, 'art' => 2));
        return $this->render('datenweitergabe/indexAuftragsverarbeitung.html.twig', [
            'table' => $daten,
            'titel' => 'Verarbeitungen nach DSGVO Art. 30(2)',
        ]);
    }

    /**
     * @Route("/datenweitergabe/new", name="datenweitergabe_new")
     */
    public function addDatenweitergabe(ValidatorInterface $validator, Request $request, DatenweitergabeService $datenweitergabeService, SecurityService $securityService)
    {
        $team = $this->getUser()->getTeam();
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $daten = $datenweitergabeService->newDatenweitergabe($this->getUser(), 1, 'DW-');

        $form = $datenweitergabeService->createForm($daten, $team);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $daten = $form->getData();
            foreach ($daten->getVerfahren() as $item) {
                $item->addDatenweitergaben($daten);
                $em->persist($item);
            }
            $errors = $validator->validate($daten);
            if (count($errors) == 0) {

                $em->persist($daten);
                $em->flush();
                return $this->redirectToRoute('datenweitergabe');
            }
        }
        return $this->render('datenweitergabe/new.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'Datenweitergabe erstellen',
            'daten' => $daten,
            'activNummer' => true,
            'activ' => $daten->getActiv()
        ]);
    }

    /**
     * @Route("/auftragsverarbeitung/new", name="auftragsverarbeitung_new")
     */
    public function addAuftragsverarbeitung(ValidatorInterface $validator, Request $request, SecurityService $securityService, DatenweitergabeService $datenweitergabeService)
    {
        $team = $this->getUser()->getTeam();
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $daten = $datenweitergabeService->newDatenweitergabe($this->getUser(), 2, 'AVV-');

        $form = $datenweitergabeService->createForm($daten, $team);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $daten = $form->getData();
            foreach ($daten->getVerfahren() as $item) {
                $item->addDatenweitergaben($daten);
                $em->persist($item);
            }

            $errors = $validator->validate($daten);
            if (count($errors) == 0) {
                $em->persist($daten);
                $em->flush();
                return $this->redirectToRoute('auftragsverarbeitung');
            }
        }
        return $this->render('datenweitergabe/new.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'Auftragsverarbeitung erstellen',
            'daten' => $daten,
            'activNummer' => true,
            'activ' => $daten->getActiv()
        ]);
    }

    /**
     * @Route("/datenweitergabe/edit", name="datenweitergabe_edit")
     */
    public function EditDatenweitergabe(ValidatorInterface $validator, Request $request, SecurityService $securityService, DatenweitergabeService $datenweitergabeService)
    {
        $team = $this->getUser()->getTeam();
        $daten = $this->getDoctrine()->getRepository(Datenweitergabe::class)->find($request->get('id'));

        if ($securityService->teamDataCheck($daten, $team) === false) {
            if ($daten->getArt() === 1) {
                return $this->redirectToRoute('datenweitergabe');
            }
            return $this->redirectToRoute('auftragsverarbeitung');
        }

        $newDaten = $datenweitergabeService->cloneDatenweitergabe($daten, $this->getUser());
        $form = $datenweitergabeService->createForm($newDaten, $team);
        $form->remove('nummer');
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $daten->setActiv(false);
            $newDaten = $form->getData();

            foreach ($newDaten->getVerfahren() as $item) {
                $item->addDatenweitergaben($newDaten);
                $em->persist($item);
            }

            $errors = $validator->validate($newDaten);
            if (count($errors) == 0) {

                $em->persist($newDaten);
                $em->persist($daten);
                $em->flush();
                return $this->redirectToRoute('datenweitergabe_edit', array('id' => $newDaten->getId(), 'snack' => 'Erfolgreich gespeichert'));
            }
        }
        return $this->render('datenweitergabe/edit.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'Datenweitergabe bearbeiten',
            'daten' => $daten,
            'activ' => $daten->getActiv(),
            'activNummer' => false,
            'snack' => $request->get('snack')
        ]);
    }

    /**
     * @Route("/datenweitergabe/download/{id}", name="datenweitergabe_download_file", methods={"GET"})
     * @ParamConverter("datenweitergabe", options={"mapping"={"id"="id"}})
     */
    public function downloadArticleReference(FilesystemInterface $internFileSystem, Datenweitergabe $datenweitergabe, SecurityService $securityService)
    {

        $stream = $internFileSystem->read($datenweitergabe->getUpload());

        $team = $this->getUser()->getTeam();
        if ($securityService->teamDataCheck($datenweitergabe, $team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $type = $internFileSystem->getMimetype($datenweitergabe->getUpload());
        $response = new Response($stream);
        $response->headers->set('Content-Type', $type);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $datenweitergabe->getUpload())
        );

        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }
}
