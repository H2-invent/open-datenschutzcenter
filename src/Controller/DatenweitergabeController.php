<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 15.05.2020
 * Time: 09:15
 */

namespace App\Controller;

use App\Entity\Datenweitergabe;
use App\Entity\DatenweitergabeGrundlagen;
use App\Entity\DatenweitergabeStand;
use App\Entity\VVT;
use App\Form\Type\DatenweitergabeType;
use App\Service\IdeaService;
use League\Flysystem\FilesystemInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class DatenweitergabeController extends AbstractController
{
    /**
     * @Route("/datenweitergabe", name="datenweitergabe")
     */
    public function indexDatenweitergabe()
    {
        $team = $this->getUser()->getTeam();
        if ($team === null) {
            return $this->redirectToRoute('dashboard');
        }

        $daten = $this->getDoctrine()->getRepository(Datenweitergabe::class)->findBy(array('team'=>$this->getUser()->getTeam(),'activ'=>true, 'art' => 1));
        return $this->render('datenweitergabe/index.html.twig', [
            'table' => $daten,
            'titel' => 'Alle Datenweitergaben nach DSGVO Art. 30(1)',
        ]);
    }

    /**
     * @Route("/auftragsverarbeitung", name="auftragsverarbeitung")
     */
    public function indexAuftragsverarbeitung()
    {
        $team = $this->getUser()->getTeam();
        if ($team === null) {
            return $this->redirectToRoute('dashboard');
        }

        $daten = $this->getDoctrine()->getRepository(Datenweitergabe::class)->findBy(array('team'=>$this->getUser()->getTeam(),'activ'=>true, 'art' => 2));
        return $this->render('datenweitergabe/indexAuftragsverarbeitung.html.twig', [
            'table' => $daten,
            'titel' => 'Verarbeitungen nach DSGVO Art. 30(2)',
        ]);
    }

    /**
     * @Route("/datenweitergabe/new", name="datenweitergabe_new")
     */
    public function addDatenweitergabe(ValidatorInterface $validator, Request $request)
    {
        $team = $this->getUser()->getTeam();
        if ($team === null) {
            return $this->redirectToRoute('dashboard');
        }

        $today = new \DateTime();
        $daten = new Datenweitergabe();
        $daten->setTeam($team);
        $daten->setNummer('DW-'. hexdec( uniqid() ));
        $daten->setActiv(true);
        $daten->setCreatedAt($today);
        $daten->setArt(1);
        $daten->setUser($this->getUser());
        $stand = $this->getDoctrine()->getRepository(DatenweitergabeStand::class)->findAll();
        $grundlagen = $this->getDoctrine()->getRepository(DatenweitergabeGrundlagen::class)->findAll();
        $verfahren = $this->getDoctrine()->getRepository(VVT::class)->findBy(array('team'=>$team,'activ'=>true));

        $form = $this->createForm(DatenweitergabeType::class, $daten, ['stand' => $stand, 'grundlage' => $grundlagen, 'kontakt' => $team->getKontakte(), 'verfahren' => $verfahren]);
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
    public function addAuftragsverarbeitung(ValidatorInterface $validator, Request $request)
    {
        $team = $this->getUser()->getTeam();
        if ($team === null) {
            return $this->redirectToRoute('dashboard');
        }

        $today = new \DateTime();
        $daten = new Datenweitergabe();
        $daten->setTeam($team);
        $daten->setActiv(true);
        $daten->setNummer('AVV-'. hexdec( uniqid() ));
        $daten->setCreatedAt($today);
        $daten->setUpdatedAt($today);
        $daten->setArt(2);
        $daten->setUser($this->getUser());
        $stand = $this->getDoctrine()->getRepository(DatenweitergabeStand::class)->findAll();
        $grundlagen = $this->getDoctrine()->getRepository(DatenweitergabeGrundlagen::class)->findAll();
        $verfahren = $this->getDoctrine()->getRepository(VVT::class)->findBy(array('team'=>$team,'activ'=>true));

        $form = $this->createForm(DatenweitergabeType::class, $daten, ['stand' => $stand, 'grundlage' => $grundlagen, 'kontakt' => $team->getKontakte(), 'verfahren' => $verfahren]);
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
    public function EditDatenweitergabe(ValidatorInterface $validator, Request $request)
    {
        $team = $this->getUser()->getTeam();
        if ($team === null) {
            return $this->redirectToRoute('fos_user_security_logout');
        }

        $today = new \DateTime();
        $daten = $this->getDoctrine()->getRepository(Datenweitergabe::class)->find($request->get('id'));

        //Sicherheitsfunktion, dass nur eigene Weitergaben bearbeitet werden können
        if ($daten-> getTeam() !== $team) {
            return $this->redirectToRoute('datenweitergabe');
        }

        $newDaten = clone $daten;
        $newDaten->setPrevious($daten);
        $newDaten->setCreatedAt($today);
        $newDaten->setUpdatedAt($today);
        $newDaten->setUser($this->getUser());

        $stand = $this->getDoctrine()->getRepository(DatenweitergabeStand::class)->findAll();
        $grundlagen = $this->getDoctrine()->getRepository(DatenweitergabeGrundlagen::class)->findAll();
        $verfahren = $this->getDoctrine()->getRepository(VVT::class)->findBy(array('team'=>$team,'activ'=>true));

        $form = $this->createForm(DatenweitergabeType::class, $newDaten, ['stand' => $stand, 'grundlage' => $grundlagen, 'kontakt' => $team->getKontakte(), 'verfahren' => $verfahren]);
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
                if ($newDaten->getArt() === 1) {
                    return $this->redirectToRoute('datenweitergabe');
                } else {
                    return $this->redirectToRoute('auftragsverarbeitung');
                }
            }
        }
        return $this->render('datenweitergabe/edit.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'Datenweitergabe bearbeiten',
            'daten' => $daten,
            'activ' => $daten->getActiv(),
            'activNummer' => false
        ]);
    }

    /**
     * @Route("/datenweitergabe/download/{id}", name="datenweitergabe_download_file", methods={"GET"})
     * @ParamConverter("datenweitergabe", options={"mapping"={"id"="id"}})
     */
    public function downloadArticleReference(FilesystemInterface $internFileSystem, Datenweitergabe $datenweitergabe)
    {

        $stream = $internFileSystem->read($datenweitergabe->getImage());

        if ($this->getUser()->getTeam() !== $datenweitergabe->getTeam()) {
            return $this->redirectToRoute('datenweitergabe_edit', ['id'=>$datenweitergabe->getId()]);
        }

        $type=$internFileSystem->getMimetype($datenweitergabe->getImage());
        $response = new Response($stream);
        $response->headers->set('Content-Type', $type);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $datenweitergabe->getImage())
        );

        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }
}
