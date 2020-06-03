<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 15.05.2020
 * Time: 09:15
 */

namespace App\Controller;

use App\Entity\AkademieBuchungen;
use App\Entity\AuditTom;
use App\Entity\Datenweitergabe;
use App\Entity\Tom;
use App\Entity\Vorfall;
use App\Entity\VVT;
use Core23\DompdfBundle\Wrapper\DompdfWrapper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BerichtController extends AbstractController
{
    /**
     * @Route("/bericht", name="bericht")
     */
    public function bericht()
    {
        // Center Team authentication
        if ($this->getUser()->getTeam() === null) {
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('bericht/index.html.twig');
    }

    /**
     * @Route("/bericht/vvt", name="bericht_vvt")
     */
    public function berichtVvt(DompdfWrapper $wrapper, Request $request)
    {
        $req = $request->get('id');
        $team = $this->getUser()->getTeam();

        if ($req) {
            $vvt = $this->getDoctrine()->getRepository(VVT::class)->findBy(array('id' => $req));
        } elseif ($request->get('daten')) {
            $vvt = $this->getDoctrine()->getRepository(VVT::class)->findBy(array('datenweitergaben' => $request->get('daten')));
        } else {
            $vvt = $this->getDoctrine()->getRepository(VVT::class)->findBy(array('team' => $team, 'activ' => true));
        }

        if (count($vvt) < 1) {
            return $this->redirectToRoute('bericht');
        }

        // Center Team authentication
        if ($team === null || $vvt[0]->getTeam() !== $team) {
            return $this->redirectToRoute('dashboard');
        }

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('bericht/vvt.html.twig', [
            'daten' => $vvt,
            'titel' => 'Verzeichnis der Verarbeitungstätigkeiten',
            'team' => $this->getUser()->getTeam(),
            'all' => $request->get('all'),
            'min' => $request->get('min'),
        ]);

        //Generate PDF File for Download
        $response = $wrapper->getStreamResponse($html, "Verarbeitungstaetigkeit.pdf");
        $response->send();

        // Send some text response
        return new Response("The PDF file has been succesfully generated !");
    }

    /**
     * @Route("/bericht/audit", name="bericht_audit")
     */
    public function berichtAudit(DompdfWrapper $wrapper, Request $request)
    {

        $req = $request->get('id');

        $team = $this->getUser()->getTeam();

        if ($req) {
            $audit = $this->getDoctrine()->getRepository(AuditTom::class)->findBy(array('id' => $req));
        } elseif ($request->get('activ')) {
            $audit = $this->getDoctrine()->getRepository(AuditTom::class)->findAuditByTeam($team);
        } elseif ($request->get('open')) {
            $audit = $this->getDoctrine()->getRepository(AuditTom::class)->findOpenByTeam($team);
        } else {
            $audit = $this->getDoctrine()->getRepository(AuditTom::class)->findBy(array('team' => $team, 'activ' => true));
        }

        if (count($audit) < 1) {
            return $this->redirectToRoute('bericht');
        }

        // Center Team authentication
        if ($team === null || $audit[0]->getTeam() !== $team) {
            return $this->redirectToRoute('dashboard');
        }

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('bericht/audit.html.twig', [
            'daten' => $audit,
            'titel' => 'Bericht zu TOM Auditfragen',
            'team' => $this->getUser()->getTeam(),
            'all' => $request->get('all'),
        ]);

        //Generate PDF File for Download
        $response = $wrapper->getStreamResponse($html, "Self-Audit.pdf");
        $response->send();

        // Send some text response
        return new Response("The PDF file has been succesfully generated !");
    }

    /**
     * @Route("/bericht/tom", name="bericht_tom")
     */
    public function berichtTom(DompdfWrapper $wrapper, Request $request)
    {

        $req = $request->get('id');
        $team = $this->getUser()->getTeam();

        if ($req) {
            $tom = $this->getDoctrine()->getRepository(Tom::class)->findBy(array('id' => $req));
        } else {
            $tom = $this->getDoctrine()->getRepository(Tom::class)->findBy(array('team' => $team, 'activ' => true));
        }

        if (count($tom) < 1) {
            return $this->redirectToRoute('bericht');
        }

        // Center Team authentication
        if ($team === null || $tom[0]->getTeam() !== $team) {
            return $this->redirectToRoute('dashboard');
        }

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('bericht/berichtTom.html.twig', [
            'datenAll' => $tom,
            'titel' => 'TOM',
            'team' => $team,
        ]);

        //Generate PDF File for Download
        $response = $wrapper->getStreamResponse($html, "Technische-und-organisatorische-Massnahmen.pdf");
        $response->send();

        // Send some text response
        return new Response("The PDF file has been succesfully generated !");
    }

    /**
     * @Route("/bericht/global_tom", name="bericht_global_tom")
     */
    public function berichtGlobalTom(DompdfWrapper $wrapper)
    {

        $team = $this->getUser()->getTeam();

        $audit = $this->getDoctrine()->getRepository(AuditTom::class)->findAllByTeam($team);

        if (count($audit) < 1) {
            return $this->redirectToRoute('bericht');
        }

        // Center Team authentication
        if ($team === null || $audit[0]->getTeam() !== $team) {
            return $this->redirectToRoute('dashboard');
        }

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('bericht/berichtGlobalTom.html.twig', [
            'daten' => $audit,
            'titel' => 'Allgemeine TOM',
            'team' => $team,
        ]);

        //Generate PDF File for Download
        $response = $wrapper->getStreamResponse($html, "Globale-TOM.pdf");
        $response->send();

        // Send some text response
        return new Response("The PDF file has been succesfully generated !");
    }

    /**
     * @Route("/bericht/weitergabe", name="bericht_weitergabe")
     */
    public function berichtWeitergabe(DompdfWrapper $wrapper, Request $request)
    {

        $req = $request->get('id');

        $team = $this->getUser()->getTeam();

        if ($req) {
            $daten = $this->getDoctrine()->getRepository(Datenweitergabe::class)->findBy(array('id' => $req));
        } else {
            $daten = $this->getDoctrine()->getRepository(Datenweitergabe::class)->findBy(array('team' => $team, 'activ' => true, 'art' => $request->get('art')));
        }

        if (count($daten) < 1) {
            return $this->redirectToRoute('bericht');
        }

        // Center Team authentication
        if ($team === null || $daten[0]->getTeam() !== $team) {
            return $this->redirectToRoute('dashboard');
        }

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('bericht/daten.html.twig', [
            'daten' => $daten,
            'titel' => 'Bericht zur Datenweitergabe',
            'team' => $this->getUser()->getTeam(),
            'all' => $request->get('all'),
        ]);

        //Generate PDF File for Download
        $response = $wrapper->getStreamResponse($html, "Datenweitergabe.pdf");
        $response->send();

        // Send some text response
        return new Response("The PDF file has been succesfully generated !");
    }

    /**
     * @Route("/bericht/akademie", name="bericht_akademie")
     */
    public function berichtAkademie()
    {

        $team = $this->getUser()->getAkademieUser();
        // Admin Team authentication
        if ($this->getUser()->getAdminUser() !== $team) {
            return $this->redirectToRoute('dashboard');
        }

        $daten = $this->getDoctrine()->getRepository(AkademieBuchungen::class)->findBerichtByTeam($team);

        return $this->render('bericht/akademie.html.twig', [
            'daten' => $daten,
            'team' => $this->getUser()->getAkademieUser()
        ]);
    }

    /**
     * @Route("/bericht/vorfall", name="bericht_vorfall")
     */
    public function berichtVorfall(DompdfWrapper $wrapper, Request $request)
    {

        $req = $request->get('id');

        $team = $this->getUser()->getTeam();

        if ($req) {
            $daten = $this->getDoctrine()->getRepository(Vorfall::class)->findBy(array('id' => $req));
        } else {
            $daten = $this->getDoctrine()->getRepository(Vorfall::class)->findBy(array('team' => $team, 'activ' => true), ['datum' => 'DESC']);
        }

        if (count($daten) < 1) {
            return $this->redirectToRoute('bericht');
        }

        // Center Team authentication
        if ($team === null || $daten[0]->getTeam() !== $team) {
            return $this->redirectToRoute('dashboard');
        }

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('bericht/vorfall.html.twig', [
            'daten' => $daten,
            'titel' => 'Bericht zu Datenvorfall',
            'team' => $this->getUser()->getTeam(),
            'all' => $request->get('all'),
        ]);

        //Generate PDF File for Download
        $response = $wrapper->getStreamResponse($html, "Datenschutzvorfall.pdf");
        $response->send();

        // Send some text response
        return new Response("The PDF file has been succesfully generated !");
    }
}
