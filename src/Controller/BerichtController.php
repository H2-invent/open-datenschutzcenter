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
use App\Repository\AkademieBuchungenRepository;
use App\Repository\AuditTomRepository;
use App\Repository\ClientRequestRepository;
use App\Repository\DatenweitergabeRepository;
use App\Repository\LoeschkonzeptRepository;
use App\Repository\PoliciesRepository;
use App\Repository\ReportRepository;
use App\Repository\SoftwareRepository;
use App\Repository\TomRepository;
use App\Repository\VorfallRepository;
use App\Repository\VVTRepository;
use App\Service\CurrentTeamService;
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
    #[Route(path: '/bericht', name: 'bericht')]
    public function bericht(Request $request,
                            CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());

        // Center Team authentication
        if (!$team) {
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('bericht/index.html.twig', [
            'snack' => $request->get('snack'),
            'currentTeam' => $team,
        ]);
    }

    #[Route(path: '/bericht/vvt', name: 'bericht_vvt')]
    public function berichtVvt(DompdfWrapper $wrapper,
                               Request $request,
                               VVTRepository $vvtRepository,
                               CurrentTeamService $currentTeamService)
    {
        ini_set('max_execution_time', '900');
        ini_set('memory_limit', '512M');

        $req = $request->get('id');
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $doc = 'Verzeichnis der Verarbeitungstätigkeiten';

        if ($req) {
            $vvt = $vvtRepository->findBy(array('id' => $req));
            $title = 'Export der Verarbeitungstätigkeit ' . $vvt[0]->getName();
            $doc = $vvt[0]->getName();
        } else {
            $vvt = $vvtRepository->findBy(array('team' => $team, 'activ' => true));
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

    #[Route(path: '/bericht/audit', name: 'bericht_audit')]
    public function berichtAudit(DompdfWrapper $wrapper,
                                 Request $request,
                                 AuditTomRepository $auditRepository,
                                 CurrentTeamService $currentTeamService)
    {

        $req = $request->get('id');

        $team = $currentTeamService->getTeamFromSession($this->getUser());

        if ($req) {
            $audit = $auditRepository->findBy(array('id' => $req));
        } elseif ($request->get('activ')) {
            $audit = $auditRepository->findAuditByTeam($team);
        } elseif ($request->get('open')) {
            $audit = $auditRepository->findActiveAndOpenByTeam($team);
        } else {
            $audit = $auditRepository->findBy(array('team' => $team, 'activ' => true));
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
            'team' => $team,
            'all' => $request->get('all'),
        ]);

        //Generate PDF File for Download
        $response = $wrapper->getStreamResponse($html, "Auditfragen.pdf");
        $response->send();
    }

    #[Route(path: '/bericht/tom', name: 'bericht_tom')]
    public function berichtTom(DompdfWrapper $wrapper,
                               Request $request,
                               TomRepository $tomRepository,
                               CurrentTeamService $currentTeamService)
    {

        $req = $request->get('id');
        $team = $currentTeamService->getTeamFromSession($this->getUser());

        if ($req) {
            $tom = $tomRepository->findBy(array('id' => $req));
        } else {
            $tom = $tomRepository->findBy(array('team' => $team, 'activ' => true));
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

    #[Route(path: '/bericht/global_tom', name: 'bericht_global_tom')]
    public function berichtGlobalTom(DompdfWrapper $wrapper,
                                     AuditTomRepository $auditRepository,
                                     CurrentTeamService $currentTeamService)
    {

        $team = $currentTeamService->getTeamFromSession($this->getUser());

        $audit = $auditRepository->findAllByTeam($team);

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

    #[Route(path: '/bericht/weitergabe', name: 'bericht_weitergabe')]
    public function berichtWeitergabe(DompdfWrapper $wrapper,
                                      Request $request,
                                      DatenweitergabeRepository $transferRepository,
                                      CurrentTeamService $currentTeamService)
    {

        $req = $request->get('id');

        $team =  $currentTeamService->getTeamFromSession($this->getUser());

        if ($req) {
            $data = $transferRepository->findBy(array('id' => $req));
        } else {
            $data = $transferRepository->findBy(array('team' => $team, 'activ' => true, 'art' => $request->get('art')));
        }

        if (count($data) < 1) {
            return $this->redirectToRoute('bericht', ['snack' => 'Keine Berichte vorhanden']);
        }

        // Center Team authentication
        if ($team === null || $data[0]->getTeam() !== $team) {
            return $this->redirectToRoute('dashboard');
        }

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('bericht/daten.html.twig', [
            'daten' => $data,
            'titel' => 'Bericht zur Datenweitergabe',
            'team' => $team,
            'all' => $request->get('all'),
        ]);

        //Generate PDF File for Download
        $response = $wrapper->getStreamResponse($html, "Datenweitergabe.pdf");
        $response->send();
    }

    #[Route(path: '/bericht/akademie', name: 'bericht_akademie')]
    public function berichtAkademie(AkademieBuchungenRepository $bookingRepository)
    {
        $user = $this->getUser();
        $team = $user->getAkademieUser();
        // Admin Team authentication
        if (!$user->hasAdminRole($team)) {
            return $this->redirectToRoute('dashboard');
        }

        $data = $bookingRepository->findBerichtByTeam($team);

        return $this->render('bericht/akademie.html.twig', [
            'daten' => $data,
            'team' => $this->getUser()->getAkademieUser()
        ]);
    }

    #[Route(path: '/bericht/vorfall', name: 'bericht_vorfall')]
    public function berichtVorfall(DompdfWrapper $wrapper,
                                   Request $request,
                                   VorfallRepository $incidentRepository,
                                   CurrentTeamService $currentTeamService)
    {

        $req = $request->get('id');

        $team =  $currentTeamService->getTeamFromSession($this->getUser());

        if ($req) {
            $data = $incidentRepository->findBy(array('id' => $req));
        } else {
            $data = $incidentRepository->findBy(array('team' => $team, 'activ' => true), ['datum' => 'DESC']);
        }

        if (count($data) < 1) {
            return $this->redirectToRoute('bericht', ['snack' => 'Keine Berichte vorhanden']);
        }

        // Center Team authentication
        if ($team === null || $data[0]->getTeam() !== $team) {
            return $this->redirectToRoute('dashboard');
        }

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('bericht/vorfall.html.twig', [
            'daten' => $data,
            'titel' => 'Bericht zu Datenvorfall',
            'team' => $team,
            'all' => $request->get('all'),
        ]);

        //Generate PDF File for Download
        $response = $wrapper->getStreamResponse($html, "Datenschutzvorfall.pdf");
        $response->send();
    }

    #[Route(path: '/bericht/policy', name: 'bericht_policy')]
    public function berichtPolicy(DompdfWrapper $wrapper,
                                  Request $request,
                                  PoliciesRepository $policyRepository,
                                  CurrentTeamService $currentTeamService)
    {

        $req = $request->get('id');

        $team = $currentTeamService->getTeamFromSession($this->getUser());

        if ($req) {
            $policies = $policyRepository->findBy(array('id' => $req));
        } else {
            $policies = $policyRepository->findBy(array('team' => $team, 'activ' => true), ['createdAt' => 'DESC']);
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
            'team' => $team,
            'all' => $request->get('all'),
        ]);

        //Generate PDF File for Download
        $response = $wrapper->getStreamResponse($html, "Richtlinie.pdf");
        $response->send();
    }

    #[Route(path: '/bericht/software', name: 'bericht_software')]
    public function berichtSoftware(DompdfWrapper $wrapper,
                                    Request $request,
                                    SoftwareRepository $softwareRepository,
                                    CurrentTeamService $currentTeamService)
    {

        $req = $request->get('id');

        $team = $currentTeamService->getTeamFromSession($this->getUser());

        if ($req) {
            $software = $softwareRepository->findBy(array('id' => $req));
        } else {
            $software = $softwareRepository->findBy(array('team' => $team, 'activ' => true), ['createdAt' => 'DESC']);
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
            'team' => $team,
            'all' => $request->get('all'),
        ]);

        //Generate PDF File for Download
        $response = $wrapper->getStreamResponse($html, "Softwarekonfiguration.pdf");
        $response->send();
    }

    #[Route(path: '/bericht/backupconcept', name: 'bericht_backupconcept')]
    public function backupSoftware(DompdfWrapper $wrapper,
                                   Request $request,
                                   VVTRepository $vvtRepository,
                                   SoftwareRepository $softwareRepository,
                                   CurrentTeamService $currentTeamService)
    {

        $team = $currentTeamService->getTeamFromSession($this->getUser());

        $software = $softwareRepository->findBy(array('team' => $team, 'activ' => true), ['createdAt' => 'DESC']);
        $vvt = $vvtRepository->findActiveByTeam($team);

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
            'team' => $team,
            'all' => $request->get('all'),
        ]);

        //Generate PDF File for Download
        $response = $wrapper->getStreamResponse($html, "Archivierungskonzept.pdf");
        $response->send();
    }

    #[Route(path: '/bericht/revoceryconcept', name: 'bericht_recoveryconcept')]
    public function recoverySoftware(DompdfWrapper $wrapper,
                                     Request $request,
                                     SoftwareRepository $softwareRepository,
                                     CurrentTeamService $currentTeamService)
    {

        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $software = $softwareRepository->findBy(array('team' => $team, 'activ' => true), ['createdAt' => 'DESC']);

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
            'team' => $team,
            'all' => $request->get('all'),
        ]);

        //Generate PDF File for Download
        $response = $wrapper->getStreamResponse($html, "Recoverykonzept.pdf");
        $response->send();
    }

    #[Route(path: '/bericht/request', name: 'bericht_request')]
    public function berichtRequest(DompdfWrapper $wrapper,
                                   Request $request,
                                   ClientRequestRepository $clientRequestRepository,
                                   CurrentTeamService $currentTeamService)
    {

        $req = $request->get('id');
        $team = $currentTeamService->getTeamFromSession($this->getUser());

        if ($req) {
            $clientRequest = $clientRequestRepository->findBy(array('id' => $req));
            $title = 'Bericht zur Kundenanfrage und Datenauskunft von ' . $clientRequest[0]->getName();
        } else {
            $clientRequest = $clientRequestRepository->findBy(array('team' => $team, 'activ' => true), ['createdAt' => 'DESC']);
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
            'team' => $team,
            'all' => $request->get('all'),
        ]);

        //Generate PDF File for Download
        $response = $wrapper->getStreamResponse($html, $title . ".pdf");
        $response->send();
    }


    #[Route(path: '/bericht/reports', name: 'bericht_reports')]
    public function berichtReports(Request $request,
                                   ReportRepository $reportRepository,
                                   CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
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
            $qb = $reportRepository->createQueryBuilder('s');
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

    #[Route(path: '/bericht/loeschkonzept', name: 'bericht_loeschkonzept')]
    public function berichtLoeschkonzept(DompdfWrapper $wrapper,
                                         Request $request,
                                         LoeschkonzeptRepository $deleteConceptRepository,
                                         CurrentTeamService $currentTeamService)
    {
        ini_set('max_execution_time', '900');
        ini_set('memory_limit', '512M');

        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $doc = 'Löschkonzepte';


        $loeschkonzept = $deleteConceptRepository->findByTeam($team);
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


    #[Route(path: '/bericht/reports/generate', name: 'bericht_reports_generate')]
    public function berichtGernateReports(Request $request, CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());

        $data = $request->get('report_export');
        $qb = $reportRepository->createQueryBuilder('s');
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
