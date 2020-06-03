<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 15.05.2020
 * Time: 09:15
 */

namespace App\Controller;

use App\Entity\AuditTomAbteilung;
use App\Entity\Datenweitergabe;
use App\Entity\Tom;
use App\Entity\VVT;
use App\Entity\VVTDatenkategorie;
use App\Entity\VVTDsfa;
use App\Entity\VVTGrundlage;
use App\Entity\VVTPersonen;
use App\Entity\VVTRisiken;
use App\Entity\VVTStatus;
use App\Form\Type\VvtDsfaType;
use App\Form\Type\VVTType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class VvtController extends AbstractController
{
    /**
     * @Route("/vvt", name="vvt")
     */
    public function index()
    {
        $team = $this->getUser()->getTeam();
        if ($team === null) {
            return $this->redirectToRoute('dashboard');
        }
        $vvt = $this->getDoctrine()->getRepository(VVT::class)->findActivByTeam($team);

        return $this->render('vvt/index.html.twig', [
            'vvt' => $vvt,

        ]);
    }

    /**
     * @Route("/vvt/new", name="vvt_new")
     */
    public function addVvt(ValidatorInterface $validator, Request $request)
    {
        $team = $this->getUser()->getTeam();
        if ($team === null) {
            return $this->redirectToRoute('dashboard');
        }

        $today = new \DateTime();
        $vvt = new VVT();
        $vvt->setTeam($team);
        $vvt->setNummer('VVT-'. hexdec( uniqid() ));
        $vvt->setCreatedAt($today);
        $vvt->setActiv(true);
        $vvt->setUser($this->getUser());

        $status = $this->getDoctrine()->getRepository(VVTStatus::class)->findAll();
        $personen = $this->getDoctrine()->getRepository(VVTPersonen::class)->findAll();
        $kategorien = $this->getDoctrine()->getRepository(VVTDatenkategorie::class)->findAll();
        $risiken = $this->getDoctrine()->getRepository(VVTRisiken::class)->findAll();
        $grundlagen = $this->getDoctrine()->getRepository(VVTGrundlage::class)->findAll();
        $datenweitergaben = $this->getDoctrine()->getRepository(Datenweitergabe::class)->findActivByTeam($team);
        $tom = $this->getDoctrine()->getRepository(Tom::class)->findBy(array('team' => $team, 'activ' => true));
        $abteilung = $this->getDoctrine()->getRepository(AuditTomAbteilung::class)->findBy(array('team' => $team, 'activ' => true));

        $form = $this->createForm(VVTType::class, $vvt, ['grundlage' => $team->getAbteilungen(), 'personen' => $personen, 'kategorien' => $kategorien, 'risiken' => $risiken, 'status' => $status, 'grundlage' => $grundlagen, 'user' => $team->getMembers(), 'daten' => $datenweitergaben, 'tom' => $tom, 'abteilung' => $abteilung]);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $vvt = $form->getData();
            $errors = $validator->validate($vvt);
            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($vvt);
                $em->flush();
                return $this->redirectToRoute('vvt');
            }
        }
        return $this->render('vvt/new.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'Verarbeitungstätigkeit erfassen',
            'activNummer' => true,
            'vvt' => $vvt,
            'activ' => $vvt->getActiv(),
            'CTA' => false,
        ]);
    }


    /**
     * @Route("/vvt/edit", name="vvt_edit")
     */
    public function editVvt(ValidatorInterface $validator, Request $request)
    {
        $team = $this->getUser()->getTeam();
        if ($team === null) {
            return $this->redirectToRoute('fos_user_security_logout');
        }
        $today = new \DateTime();
        $vvt = $this->getDoctrine()->getRepository(VVT::class)->find($request->get('id'));

        //Sicherheitsfunktion, dass nur eigene VVTs bearbeitet werden können
        if ($vvt->getTeam() !== $team) {
            return $this->redirectToRoute('vvt');
        }

        $newVvt = clone $vvt;

        $newVvt->setPrevious($vvt);
        $newVvt->setActiv(true);
        $newVvt->setUser($this->getUser());


        $status = $this->getDoctrine()->getRepository(VVTStatus::class)->findAll();
        $personen = $this->getDoctrine()->getRepository(VVTPersonen::class)->findAll();
        $kategorien = $this->getDoctrine()->getRepository(VVTDatenkategorie::class)->findAll();
        $risiken = $this->getDoctrine()->getRepository(VVTRisiken::class)->findAll();
        $grundlagen = $this->getDoctrine()->getRepository(VVTGrundlage::class)->findAll();
        $daten = $this->getDoctrine()->getRepository(Datenweitergabe::class)->findBy(array('team' => $team, 'activ' => true));
        $tom = $this->getDoctrine()->getRepository(Tom::class)->findBy(array('team' => $team, 'activ' => true));
        $abteilung = $this->getDoctrine()->getRepository(AuditTomAbteilung::class)->findBy(array('team' => $team, 'activ' => true));

        $form = $this->createForm(VVTType::class, $newVvt, ['grundlage' => $team->getAbteilungen(), 'personen' => $personen, 'kategorien' => $kategorien, 'risiken' => $risiken, 'status' => $status, 'grundlage' => $grundlagen, 'user' => $team->getMembers(), 'daten' => $daten, 'tom' => $tom, 'abteilung' => $abteilung]);
        $form->remove('nummer');
        $form->handleRequest($request);
        $errors = array();

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $vvt->setActiv(false);
            $newVvt = $form->getData();
            $newVvt->setCreatedAt($today);

            $errors = $validator->validate($newVvt);
            if (count($errors) == 0) {

                if ($vvt->getActivDsfa()) {
                    $dsfa = $vvt->getActivDsfa();
                    $dsfa->setVvt($newVvt);
                    $em->persist($dsfa);
                }

                $em->persist($newVvt);
                $em->persist($vvt);
                $em->flush();
                return $this->redirectToRoute('vvt');
            }
        }

        return $this->render('vvt/edit.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'Verarbeitungstätigkeit bearbeiten',
            'vvt' => $vvt,
            'activ' => $vvt->getActiv(),
            'activNummer' => false,
        ]);
    }


    /**
     * @Route("/vvt/dsfa/new", name="vvt_dsfa_new")
     */
    public function newVvtDsfa(ValidatorInterface $validator, Request $request)
    {
        $team = $this->getUser()->getTeam();
        if ($team === null) {
            return $this->redirectToRoute('fos_user_security_logout');
        }
        $today = new \DateTime();
        $vvt = $this->getDoctrine()->getRepository(VVT::class)->find($request->get('vvt'));

        $dsfa = new VVTDsfa();
        $dsfa->setVvt($vvt);
        $dsfa->setActiv(true);
        $dsfa->setCreatedAt($today);
        $dsfa->setUser($this->getUser());

        $form = $this->createForm(VvtDsfaType::class, $dsfa);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $dsfa = $form->getData();
            $errors = $validator->validate($dsfa);
            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($dsfa);
                $em->flush();
                return $this->redirectToRoute('vvt');
            }
        }
        return $this->render('vvt/editDsfa.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'Datenschutzfolgeabschätzung erstellen',
            'dsfa' => $dsfa,
            'activ' => $dsfa->getActiv(),
        ]);
    }

    /**
     * @Route("/vvt/dsfa/edit", name="vvt_dsfa_edit")
     */
    public function editVvtDsfa(ValidatorInterface $validator, Request $request)
    {
        $team = $this->getUser()->getTeam();
        if ($team === null) {
            return $this->redirectToRoute('fos_user_security_logout');
        }
        $today = new \DateTime();
        $dsfa = $this->getDoctrine()->getRepository(VVTDsfa::class)->find($request->get('dsfa'));

        //Sicherheitsfunktion, dass nur eigene DSFAs bearbeitet werden können
        if ($dsfa->getVvt()->getTeam() !== $team) {
            return $this->redirectToRoute('vvt');
        }

        $newDsfa = clone $dsfa;
        $newDsfa->setPrevious($dsfa);
        $newDsfa->setVvt($dsfa->getVvt());
        $newDsfa->setActiv(true);
        $newDsfa->setCreatedAt($today);
        $newDsfa->setUser($this->getUser());

        $form = $this->createForm(VvtDsfaType::class, $newDsfa);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $dsfa->setActiv(false);
            $newDsfa = $form->getData();
            $errors = $validator->validate($newDsfa);
            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($newDsfa);
                $em->persist($dsfa);
                $em->flush();
                return $this->redirectToRoute('vvt');
            }
        }

        return $this->render('vvt/editDsfa.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'Datenschutzfolgeabschätzung bearbeiten',
            'dsfa' => $dsfa,
            'activ' => $dsfa->getActiv(),
        ]);
    }
}
