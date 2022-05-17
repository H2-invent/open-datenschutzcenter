<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 15.05.2020
 * Time: 09:15
 */

 /**
 * Modified by
 * User: Jan Juister
 * Date: 13.05.2022
 */

namespace App\Controller;

use App\Entity\AkademieBuchungen;
use App\Entity\AuditTom;
use App\Entity\ClientRequest;
use App\Entity\Datenweitergabe;
use App\Entity\Policies;
use App\Entity\Report;
use App\Entity\Software;
use App\Entity\Tom;
use App\Entity\Vorfall;
use App\Entity\VVT;
use App\Entity\Loeschkonzept;
use App\Entity\VVTDatenkategorie;
use App\Form\Type\ReportExportType;
use Nucleos\DompdfBundle\Wrapper\DompdfWrapper;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class BerichtController extends AbstractController
{
    /**
     * @Route("/bericht", name="bericht")
     */
    public function bericht(Request $request)
    {
        // Center Team authentication
        if ($this->getUser()->getTeam() === null) {
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('bericht/index.html.twig', ['snack' => $request->get('snack')]);
    }

    /**
     * @Route("/bericht/vvt", name="bericht_vvt")
     */
    public function berichtVvt(DompdfWrapper $wrapper, Request $request)
    {
        ini_set('max_execution_time', '900');
        ini_set('memory_limit', '512M');

        $req = $request->get('id');
        $team = $this->getUser()->getTeam();
        $doc = 'Verzeichnis der Verarbeitungstätigkeiten';

        if ($req) {
            $vvt = $this->getDoctrine()->getRepository(VVT::class)->findBy(array('id' => $req));
            $title = 'Export der Verarbeitungstätigkeit ' . $vvt[0]->getName();
            $doc = $vvt[0]->getName();
        } else {
            $vvt = $this->getDoctrine()->getRepository(VVT::class)->findBy(array('team' => $team, 'activ' => true));
            $title = 'Verzeichnis der Verarbeitungstätigkeiten von ' . $team->getName();
        }

        if (count($vvt) < 1) {
            return $this->redirectToRoute('bericht', ['snack' => 'Keine Berichte vorhanden']);
        }

        // Center Team authentication
        if ($team === null || $vvt[0]->getTeam() !== $team) {
            return $this->redirectToRoute('dashboard');
        }

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('bericht/vvt.html.twig', [
            'daten' => $vvt,
            'titel' => $title,
            'team' => $team,
            'all' => $request->get('all'),
            'min' => $request->get('min'),
        ]);

        //Generate PDF File for Download
        $response = $wrapper->getStreamResponse($html, $doc . ".pdf");
        $response->send();
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
            return $this->redirectToRoute('bericht', ['snack' => 'Keine Berichte vorhanden']);
        }

        // Center Team authentication
        if ($team === null || $audit[0]->getTeam() !== $team) {
            return $this->redirectToRoute('dashboard');
        }

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('bericht/audit.html.twig', [
            'daten' => $audit,
            'titel' => 'Bericht zu Auditfragen',
            'team' => $this->getUser()->getTeam(),
            'all' => $request->get('all'),
        ]);

        //Generate PDF File for Download
        $response = $wrapper->getStreamResponse($html, "Auditfragen.pdf");
        $response->send();
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
            return $this->redirectToRoute('bericht', ['snack' => 'Keine Berichte vorhanden']);
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
    }

    /**
     * @Route("/bericht/global_tom", name="bericht_global_tom")
     */
    public function berichtGlobalTom(DompdfWrapper $wrapper)
    {

        $team = $this->getUser()->getTeam();

        $audit = $this->getDoctrine()->getRepository(AuditTom::class)->findAllByTeam($team);

        if (count($audit) < 1) {
            return $this->redirectToRoute('bericht', ['snack' => 'Keine Berichte vorhanden']);
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
        $response = $wrapper->getStreamResponse($html, "Globale_TOM.pdf");
        $response->send();
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
            return $this->redirectToRoute('bericht', ['snack' => 'Keine Berichte vorhanden']);
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
            return $this->redirectToRoute('bericht', ['snack' => 'Keine Berichte vorhanden']);
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
    }

    /**
     * @Route("/bericht/policy", name="bericht_policy")
     */
    public function berichtPolicy(DompdfWrapper $wrapper, Request $request)
    {

        $req = $request->get('id');

        $team = $this->getUser()->getTeam();

        if ($req) {
            $policies = $this->getDoctrine()->getRepository(Policies::class)->findBy(array('id' => $req));
        } else {
            $policies = $this->getDoctrine()->getRepository(Policies::class)->findBy(array('team' => $team, 'activ' => true), ['createdAt' => 'DESC']);
        }

        if (count($policies) < 1) {
            return $this->redirectToRoute('bericht', ['snack' => 'Keine Berichte vorhanden']);
        }

        // Center Team authentication
        if ($team === null || $policies[0]->getTeam() !== $team) {
            return $this->redirectToRoute('dashboard');
        }

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('bericht/policy.html.twig', [
            'daten' => $policies,
            'titel' => 'Bericht zu Datenschutzrichtlinien',
            'team' => $this->getUser()->getTeam(),
            'all' => $request->get('all'),
        ]);

        //Generate PDF File for Download
        $response = $wrapper->getStreamResponse($html, "Richtlinie.pdf");
        $response->send();
    }

    /**
     * @Route("/bericht/software", name="bericht_software")
     */
    public function berichtSoftware(DompdfWrapper $wrapper, Request $request)
    {

        $req = $request->get('id');

        $team = $this->getUser()->getTeam();

        if ($req) {
            $software = $this->getDoctrine()->getRepository(Software::class)->findBy(array('id' => $req));
        } else {
            $software = $this->getDoctrine()->getRepository(Software::class)->findBy(array('team' => $team, 'activ' => true), ['createdAt' => 'DESC']);
        }

        if (count($software) < 1) {
            return $this->redirectToRoute('bericht', ['snack' => 'Keine Berichte vorhanden']);
        }

        // Center Team authentication
        if ($team === null || $software[0]->getTeam() !== $team) {
            return $this->redirectToRoute('dashboard');
        }
        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('bericht/software.html.twig', [
            'daten' => $software,
            'titel' => 'Bericht zu verwendeter Software und Konfiguration',
            'team' => $this->getUser()->getTeam(),
            'all' => $request->get('all'),
        ]);

        //Generate PDF File for Download
        $response = $wrapper->getStreamResponse($html, "Softwarekonfiguration.pdf");
        $response->send();
    }

    /**
     * @Route("/bericht/backupconcept", name="bericht_backupconcept")
     */
    public function backupSoftware(DompdfWrapper $wrapper, Request $request)
    {

        $team = $this->getUser()->getTeam();

        $software = $this->getDoctrine()->getRepository(Software::class)->findBy(array('team' => $team, 'activ' => true), ['createdAt' => 'DESC']);
        $vvt = $this->getDoctrine()->getRepository(VVT::class)->findActivByTeam($team);

        if (count($software) < 1) {
            return $this->redirectToRoute('bericht', ['snack' => 'Keine Berichte vorhanden']);
        }

        // Center Team authentication
        if ($team === null || $software[0]->getTeam() !== $team) {
            return $this->redirectToRoute('dashboard');
        }

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('bericht/backup.html.twig', [
            'daten' => $software,
            'vvt' => $vvt,
            'titel' => 'Archivierungskonzept',
            'team' => $this->getUser()->getTeam(),
            'all' => $request->get('all'),
        ]);

        //Generate PDF File for Download
        $response = $wrapper->getStreamResponse($html, "Archivierungskonzept.pdf");
        $response->send();
    }

    /**
     * @Route("/bericht/revoceryconcept", name="bericht_recoveryconcept")
     */
    public function recoverySoftware(DompdfWrapper $wrapper, Request $request)
    {

        $team = $this->getUser()->getTeam();
        $software = $this->getDoctrine()->getRepository(Software::class)->findBy(array('team' => $team, 'activ' => true), ['createdAt' => 'DESC']);

        if (count($software) < 1) {
            return $this->redirectToRoute('bericht', ['snack' => 'Keine Berichte vorhanden']);
        }

        // Center Team authentication
        if ($team === null || $software[0]->getTeam() !== $team) {
            return $this->redirectToRoute('dashboard');
        }

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('bericht/recovery.html.twig', [
            'daten' => $software,
            'titel' => 'Recoverykonzept und Widerherstellungskonzept',
            'team' => $this->getUser()->getTeam(),
            'all' => $request->get('all'),
        ]);

        //Generate PDF File for Download
        $response = $wrapper->getStreamResponse($html, "Recoverykonzept.pdf");
        $response->send();
    }

    /**
     * @Route("/bericht/request", name="bericht_request")
     */
    public function berichtRequest(DompdfWrapper $wrapper, Request $request)
    {

        $req = $request->get('id');
        $team = $this->getUser()->getTeam();

        if ($req) {
            $clientRequest = $this->getDoctrine()->getRepository(ClientRequest::class)->findBy(array('id' => $req));
            $title = 'Bericht zur Kundenanfrage und Datenauskunft von ' . $clientRequest[0]->getName();
        } else {
            $clientRequest = $this->getDoctrine()->getRepository(ClientRequest::class)->findBy(array('team' => $team, 'activ' => true), ['createdAt' => 'DESC']);
            $title = 'Bericht zur Kundenanfrage und Datenauskunft';
        }

        if (count($clientRequest) < 1) {
            return $this->redirectToRoute('bericht', ['snack' => 'Keine Berichte vorhanden']);
        }

        // Center Team authentication
        if ($team === null || $clientRequest[0]->getTeam() !== $team) {
            return $this->redirectToRoute('dashboard');
        }

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('bericht/request.html.twig', [
            'daten' => $clientRequest,
            'titel' => $title,
            'team' => $this->getUser()->getTeam(),
            'all' => $request->get('all'),
        ]);

        //Generate PDF File for Download
        $response = $wrapper->getStreamResponse($html, $title . ".pdf");
        $response->send();
    }


    /**
     * @Route("/bericht/reports", name="bericht_reports")
     */
    public function berichtReports(Request $request)
    {
        $team = $this->getUser()->getTeam();
        $users = $team->getMembers();

        $members = array();
        foreach ($users as $item) {
            $members[$item->getEmail()] = $item->getId();
        }

        $form = $this->createForm(ReportExportType::class, ['action' => $this->generateUrl('bericht_reports'), 'method' => 'GET']);
        $form->handleRequest($request);

        $title = 'Tätigkeitsbericht erstellen';

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();
            $qb = $this->getDoctrine()->getRepository(Report::class)->createQueryBuilder('s');
            $qb->andWhere(
                $qb->expr()->between('s.date', ':von', ':bis')
            )
                ->andWhere('s.activ = 1')
                ->setParameter('von', $data['von'])
                ->setParameter('bis', $data['bis']);

            if ($data['user'] !== null) {
                $qb->innerJoin('s.user', 'u')
                    ->andWhere('u.email = :user')
                    ->setParameter('user', $data['user']);
            }

            if ($data['report'] === 1) {
                $qb->andWhere('s.inReport = 1');
            }

            $report = $qb->getQuery()->getResult();

            if (count($report) < 1) {
                return $this->redirectToRoute('bericht', ['snack' => 'Keine Berichte vorhanden']);
            }

            // Center Team authentication
            if ($team === null || $report[0]->getTeam() !== $team) {
                return $this->redirectToRoute('dashboard');
            }


            // Create a new Word document
            $phpWord = new PhpWord();
            $phpWord->addTitleStyle(1, array('bold' => true), array('spaceAfter' => 240));
            $phpWord->addTitleStyle(2, array('bold' => true), array('spaceBefore' => 300));
            $header = array('size' => 34, 'bold' => true);
            $secHeader = array('size' => 13, 'bold' => true);

            $title = $data['title'];

            $section = $phpWord->addSection();
            $section->addText($title, $header);

            foreach ($report as $item) {

                // Adding a software to the document...
                $section->addTitle($item->getDate()->format('d.m.Y'), 2);

                $table = $section->addTable();
                $table->addRow();
                $table->addCell(100 * 50)->addText('Datum');
                $table->addCell(100 * 50)->addText($item->getDate()->format('d.m.Y'));

                $table->addRow();
                $table->addCell()->addText('Startzeit');
                $table->addCell()->addText($item->getStart()->format('H:i'));

                $table->addRow();
                $table->addCell()->addText('Endzeit');
                $table->addCell()->addText($item->getEnd()->format('H:i'));

                $table->addRow();
                $table->addCell()->addText('Bearbeiter');
                $table->addCell()->addText($item->getUser()->getEmail());

                $table->addRow();
                $table->addCell()->addText('Vor Ort');
                $table->addCell()->addText($item->getOnSite() ? 'Ja' : 'Nein');

                $table->addRow();
                $table->addCell()->addText('Beschreibung');
                $table->addCell()->addText($item->getDescription());
            }

            $section->addHeader()->addText($title);
            $section->addFooter()->addText('Powered by open-datenschutzcenter.de');

            // Saving the document as OOXML file...
            $objWriter = IOFactory::createWriter($phpWord, 'Word2007');

            // Create a temporal file in the system
            $fileName = $data['title'] . '.docx';
            $temp_file = tempnam(sys_get_temp_dir(), $fileName);

            // Write in the temporal filepath
            $objWriter->save($temp_file);

            // Send the temporal file as response (as an attachment)
            $response = new BinaryFileResponse($temp_file);
            $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $fileName
            );

            return $response;
        }

        return $this->render('bericht/modalView.html.twig', array('form' => $form->createView(), 'title' => $title, 'members' => $users));
    }

    /**
     * @Route("/bericht/loeschkonzept", name="bericht_loeschkonzept")
     */
    public function berichtLoeschkonzept(DompdfWrapper $wrapper, Request $request)
    {
        ini_set('max_execution_time', '900');
        ini_set('memory_limit', '512M');

        $req = $request->get('id');
        $team = $this->getUser()->getTeam();
        $doc = 'Löschkonzepte';


        $loeschkonzept = $this->getDoctrine()->getRepository(Loeschkonzept::class)->findByTeam($team);
        $title = 'Verzeichnis der Löschkonzepte von ' . $team->getName();

        if (count($loeschkonzept) < 1) {
            return $this->redirectToRoute('bericht', ['snack' => 'Keine Berichte vorhanden']);
        }

        // Center Team authentication
        if ($team === null || $loeschkonzept[0]->getTeam() !== $team) {
            return $this->redirectToRoute('dashboard');
        }

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('bericht/loeschkonzept.html.twig', [
            'daten' => $loeschkonzept,
            'titel' => $title,
            'team' => $team,
            'all' => $request->get('all'),
        ]);

        //Generate PDF File for Download
        $response = $wrapper->getStreamResponse($html, $doc . ".pdf");
        $response->send();
    }


    /**
     * @Route("/bericht/reports/generate", name="bericht_reports_generate")
     */
    public function berichtGernateReports(Request $request)
    {
        $team = $this->getUser()->getTeam();

        $data = $request->get('report_export');
        $qb = $this->getDoctrine()->getRepository(Report::class)->createQueryBuilder('s');
        $qb->andWhere(
            $qb->expr()->between('s.date', ':von', ':bis')
        )
            ->andWhere('s.activ = 1')
            ->setParameter('von', $data['von'])
            ->setParameter('bis', $data['bis']);

        if ($data['user'] !== null) {
            $qb->innerJoin('s.user', 'u')
                ->andWhere('u.email = :user')
                ->setParameter('user', $data['user']);
        }

        if ($data['report'] === 1) {
            $qb->andWhere('s.inReport = 1');
        }

        $report = $qb->getQuery()->getResult();

        if (count($report) < 1) {
            return $this->redirectToRoute('bericht', ['snack' => 'Keine Berichte vorhanden']);
        }

        // Center Team authentication
        if ($team === null || $report[0]->getTeam() !== $team) {
            return $this->redirectToRoute('dashboard');
        }


        // Create a new Word document
        $phpWord = new PhpWord();
        $phpWord->addTitleStyle(1, array('bold' => true), array('spaceAfter' => 240));
        $phpWord->addTitleStyle(2, array('bold' => true), array('spaceBefore' => 300));
        $header = array('size' => 34, 'bold' => true);
        $secHeader = array('size' => 13, 'bold' => true);

        $title = $data['title'];

        $section = $phpWord->addSection();
        $section->addText($title, $header);

        foreach ($report as $item) {

            // Adding a software to the document...
            $section->addTitle($item->getDate()->format('d.m.Y'), 2);

            $table = $section->addTable();
            $table->addRow();
            $table->addCell(100 * 50)->addText('Datum');
            $table->addCell(100 * 50)->addText($item->getDate()->format('d.m.Y'));

            $table->addRow();
            $table->addCell()->addText('Startzeit');
            $table->addCell()->addText($item->getStart()->format('H:i'));

            $table->addRow();
            $table->addCell()->addText('Endzeit');
            $table->addCell()->addText($item->getStart()->format('H:i'));

            $table->addRow();
            $table->addCell()->addText('Bearbeiter');
            $table->addCell()->addText($item->getUser()->getEmail());

            $table->addRow();
            $table->addCell()->addText('Vor Ort');
            $table->addCell()->addText($item->getOnSite() ? 'Ja' : 'Nein');

            $section->addText('Beschreibung', $secHeader);
            $section->addText($item->getDescription());
        }

        $section->addHeader()->addText($title);
        $section->addFooter()->addText('Powered by open-datenschutzcenter.de');

        // Saving the document as OOXML file...
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');

        // Create a temporal file in the system
        $fileName = $data['title'] . '.docx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        // Write in the temporal filepath
        $objWriter->save($temp_file);

        // Send the temporal file as response (as an attachment)
        $response = new BinaryFileResponse($temp_file);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $fileName
        );

        return $response;
    }
}
