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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/bericht', name: 'bericht')]
class BerichtController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    )
    {
    }

    #[Route(path: '/backupconcept', name: '_backupconcept')]
    public function backupSoftware(
        DompdfWrapper      $wrapper,
        Request            $request,
        CurrentTeamService $currentTeamService,
        SoftwareRepository $softwareRepository,
        VVTRepository      $vvtRepository,
    )
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());

        $software = $softwareRepository->findBy(['team' => $team, 'activ' => true], ['createdAt' => 'DESC']);
        $vvt = $vvtRepository->findActiveByTeam($team);

        if (count($software) < 1) {
            return $this->redirectNoReport();
        }

        // Center Team authentication
        if ($team === null || $software[0]->getTeam() !== $team) {
            return $this->redirectToRoute('dashboard');
        }

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('bericht/backup.html.twig', [
            'daten' => $software,
            'vvt' => $vvt,
            'titel' => $this->translator->trans(id: 'archiveConcept.word', domain: 'bericht'),
            'team' => $team,
            'all' => $request->get('all'),
        ]);

        //Generate PDF File for Download
        $response = $wrapper->getStreamResponse(
            $html,
            $this->translator->trans(id: 'archiveConcept.word', domain: 'bericht') . '.pdf'
        );
        $response->send();
    }

    #[Route(path: '/revoceryconcept', name: '_recoveryconcept')]
    public function recoverySoftware(
        DompdfWrapper      $wrapper,
        Request            $request,
        CurrentTeamService $currentTeamService,
        SoftwareRepository $softwareRepository,
    )
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        $software = $softwareRepository->findBy(['team' => $team, 'activ' => true], ['createdAt' => 'DESC']);

        if (count($software) < 1) {
            return $this->redirectNoReport();
        }

        // Center Team authentication
        if ($team === null || $software[0]->getTeam() !== $team) {
            return $this->redirectToRoute('dashboard');
        }

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('bericht/recovery.html.twig', [
            'daten' => $software,
            'titel' => $this->translator->trans(id: 'report.about.recoveryConcept', domain: 'bericht'),
            'team' => $team,
            'all' => $request->get('all'),
        ]);

        //Generate PDF File for Download
        $response = $wrapper->getStreamResponse(
            $html,
            $this->translator->trans(id: 'recoveryConcept', domain: 'bericht')
        );
        $response->send();
    }

    #[Route(path: '', name: '')]
    public function report(
        Request            $request,
        CurrentTeamService $currentTeamService,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());

        // Center Team authentication
        if (!$team) {
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('bericht/index.html.twig', [
            'snack' => $request->get('snack'),
            'currentTeam' => $team,
        ]);
    }

    #[Route(path: '/akademie', name: '_akademie')]
    public function reportAcademy(
        AkademieBuchungenRepository $academyBillingRepository,
    ): Response
    {
        $user = $this->getUser();
        $team = $user->getAkademieUser();
        // Admin Team authentication
        if (!$user->hasAdminRole($team)) {
            return $this->redirectToRoute('dashboard');
        }

        $daten = $academyBillingRepository->findBerichtByTeam($team);

        return $this->render('bericht/akademie.html.twig', [
            'daten' => $daten,
            'team' => $this->getUser()->getAkademieUser()
        ]);
    }

    #[Route(path: '/audit', name: '_audit')]
    public function reportAudit(
        DompdfWrapper      $wrapper,
        Request            $request,
        CurrentTeamService $currentTeamService,
        AuditTomRepository $auditTomRepository,
    )
    {

        $req = $request->get('id');

        $team = $currentTeamService->getCurrentTeam($this->getUser());

        if ($req) {
            $audit = $auditTomRepository->findBy(array('id' => $req));
        } elseif ($request->get('activ')) {
            $audit = $auditTomRepository->findAuditByTeam($team);
        } elseif ($request->get('open')) {
            $audit = $auditTomRepository->findActiveAndOpenByTeam($team);
        } else {
            $audit = $auditTomRepository->findBy(array('team' => $team, 'activ' => true));
        }

        if (count($audit) < 1) {
            return $this->redirectNoReport();
        }

        // Center Team authentication
        if ($team === null || $audit[0]->getTeam() !== $team) {
            return $this->redirectToRoute('dashboard');
        }

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('bericht/audit.html.twig', [
            'daten' => $audit,
            'titel' => $this->translator->trans(id: 'report.about.auditQuestion', domain: 'bericht'),
            'team' => $team,
            'all' => $request->get('all'),
        ]);

        //Generate PDF File for Download
        $response = $wrapper->getStreamResponse($html, $this->translator->trans(id: 'auditQuestion.word', domain: 'audit_tom') . '.pdf');
        $response->send();
    }

    #[Route(path: '/weitergabe', name: '_weitergabe')]
    public function reportDataTransfer(
        DompdfWrapper             $wrapper,
        Request                   $request,
        CurrentTeamService        $currentTeamService,
        DatenweitergabeRepository $dataTransferRepository,
    )
    {
        $id = $request->get('id');
        $team = $currentTeamService->getCurrentTeam($this->getUser());

        if ($id) {
            $daten = $dataTransferRepository->findBy(['id'=>$id]);
        } else {
            $daten = $dataTransferRepository->findBy([
                'team' => $team,
                'activ' => true,
                'art' => $request->get('art')
            ]);
        }

        if (count($daten) < 1) {
            return $this->redirectNoReport();
        }

        // Center Team authentication
        if ($team === null || $daten[0]->getTeam() !== $team) {
            return $this->redirectToRoute('dashboard');
        }

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('bericht/daten.html.twig', [
            'daten' => $daten,
            'titel' => $this->translator->trans(id: 'report.about.dataTransfer', domain: 'bericht'),
            'team' => $team,
            'all' => $request->get('all'),
        ]);

        //Generate PDF File for Download
        $response = $wrapper->getStreamResponse(
            $html,
            $this->translator->trans(id: 'dataTransfer.word', domain: 'datenweitergabe') . '.pdf',
        );
        $response->send();
    }

    #[Route(path: '/loeschkonzept', name: '_loeschkonzept')]
    public function reportDeletionConcept(
        DompdfWrapper           $wrapper,
        Request                 $request,
        CurrentTeamService      $currentTeamService,
        LoeschkonzeptRepository $deletionConceptRepository,
    )
    {
        ini_set('max_execution_time', '900');
        ini_set('memory_limit', '512M');

        $team = $currentTeamService->getCurrentTeam($this->getUser());
        $doc = $this->translator->trans(id: 'deletionConcept.plural', domain: 'loeschkonzept');


        $loeschkonzept = $deletionConceptRepository->findByTeam($team);
        $title = $this->translator->trans(id: 'deletionConcept.directoryOf') . ' ' . $team->getName();

        if (count($loeschkonzept) < 1) {
            return $this->redirectNoReport();
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

    #[Route(path: '/reports/generate', name: '_reports_generate')]
    public function reportGenerateReports(
        Request            $request,
        CurrentTeamService $currentTeamService,
        ReportRepository   $reportRepository,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());

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
            return $this->redirectNoReport();
        }

        // Center Team authentication
        if ($team === null || $report[0]->getTeam() !== $team) {
            return $this->redirectToRoute('dashboard');
        }


        // Create a new Word document
        $phpWord = new PhpWord();
        $phpWord->addTitleStyle(1, ['bold' => true], ['spaceAfter' => 240]);
        $phpWord->addTitleStyle(2, ['bold' => true], ['spaceBefore' => 300]);
        $header = ['size' => 34, 'bold' => true];
        $secHeader = ['size' => 13, 'bold' => true];

        $title = $data['title'];

        $section = $phpWord->addSection();
        $section->addText($title, $header);

        foreach ($report as $item) {

            // Adding a software to the document...
            $section->addTitle($item->getDate()->format('d.m.Y'), 2);

            $table = $section->addTable();
            $table->addRow();
            $table->addCell(100 * 50)->addText($this->translator->trans(id: 'date', domain: 'general'));
            $table->addCell(100 * 50)->addText($item->getDate()->format('d.m.Y'));

            $table->addRow();
            $table->addCell()->addText($this->translator->trans(id: 'startTime', domain: 'general'));
            $table->addCell()->addText($item->getStart()->format('H:i'));

            $table->addRow();
            $table->addCell()->addText($this->translator->trans(id: 'endTime', domain: 'general'));
            $table->addCell()->addText($item->getEnd()->format('H:i'));

            $table->addRow();
            $table->addCell()->addText($this->translator->trans(id: 'worker', domain: 'report'));
            $table->addCell()->addText($item->getUser()->getEmail());

            $table->addRow();
            $table->addCell()->addText($this->translator->trans(id: 'work.onSight', domain: 'report'));
            $table->addCell()->addText($this->translator->trans(id: ($item->getOnSite() ? 'yes' : 'no'), domain: 'general'));

            $table->addRow();
            $table->addCell()->addText($this->translator->trans(id: 'description', domain: 'general'));
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

    #[Route(path: '/global_tom', name: '_global_tom')]
    public function reportGlobalTom(
        DompdfWrapper      $wrapper,
        CurrentTeamService $currentTeamService,
        AuditTomRepository $auditTomRepository,
    )
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        $audit = $auditTomRepository->findAllByTeam($team);

        if (count($audit) < 1) {
            return $this->redirectNoReport();
        }

        // Center Team authentication
        if ($team === null || $audit[0]->getTeam() !== $team) {
            return $this->redirectToRoute('dashboard');
        }

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('bericht/berichtGlobalTom.html.twig', [
            'daten' => $audit,
            'titel' => $this->translator->trans(id: 'tom.general', domain: 'bericht'),
            'team' => $team,
        ]);

        //Generate PDF File for Download
        $response = $wrapper->getStreamResponse(
            $html,
            $this->translator->trans(id: 'tom.global', domain: 'bericht') . '.pdf',
        );
        $response->send();
    }

    #[Route(path: '/vorfall', name: '_vorfall')]
    public function reportIncident(
        DompdfWrapper      $wrapper,
        Request            $request,
        CurrentTeamService $currentTeamService,
        VorfallRepository  $vorfallRepository,
    )
    {
        $id = $request->get('id');

        $team = $currentTeamService->getCurrentTeam($this->getUser());

        if ($id) {
            $daten = $vorfallRepository->findBy(['id'=>$id]);
        } else {
            $daten = $vorfallRepository->findBy(['team' => $team, 'activ' => true], ['datum' => 'DESC']);
        }

        if (count($daten) < 1) {
            return $this->redirectNoReport();
        }

        // Center Team authentication
        if ($team === null || $daten[0]->getTeam() !== $team) {
            return $this->redirectToRoute('dashboard');
        }

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('bericht/vorfall.html.twig', [
            'daten' => $daten,
            'titel' => $this->translator->trans(id: 'report.about.dataIncident', domain: 'bericht'),
            'team' => $team,
            'all' => $request->get('all'),
        ]);

        //Generate PDF File for Download
        $response = $wrapper->getStreamResponse(
            $html,
            $this->translator->trans(id: 'dataProtection.incident', domain: 'bericht') . '.pdf',
        );
        $response->send();
    }

    #[Route(path: '/policy', name: '_policy')]
    public function reportPolicy(
        DompdfWrapper      $wrapper,
        Request            $request,
        CurrentTeamService $currentTeamService,
        PoliciesRepository $policiesRepository,
    )
    {
        $id = $request->get('id');
        $team = $currentTeamService->getCurrentTeam($this->getUser());

        if ($id) {
            $policies = $policiesRepository->findBy(['id'=>$id]);
        } else {
            $policies = $policiesRepository->findBy(['team' => $team, 'activ' => true], ['createdAt' => 'DESC']);
        }

        if (count($policies) < 1) {
            return $this->redirectNoReport();
        }

        // Center Team authentication
        if ($team === null || $policies[0]->getTeam() !== $team) {
            return $this->redirectToRoute('dashboard');
        }

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('bericht/policy.html.twig', [
            'daten' => $policies,
            'titel' => $this->translator->trans(id: 'report.about.dataProtectionGuidelines', domain: 'bericht'),
            'team' => $team,
            'all' => $request->get('all'),
        ]);

        //Generate PDF File for Download
        $response = $wrapper->getStreamResponse(
            $html,
            $this->translator->trans(id: 'guideline', domain: 'vorfall') . '.pdf',
        );
        $response->send();
    }

    #[Route(path: '/request', name: '_request')]
    public function reportRequest(
        DompdfWrapper           $wrapper,
        Request                 $request,
        CurrentTeamService      $currentTeamService,
        ClientRequestRepository $clientRequestRepository,
    )
    {

        $id = $request->get('id');
        $team = $currentTeamService->getCurrentTeam($this->getUser());

        if ($id) {
            $clientRequest = $clientRequestRepository->findBy(['id'=>$id]);
            $title = $this->translator->trans(id: 'report.about.clientRequestBy', domain: 'bericht') . ' ' . $clientRequest->getName();
        } else {
            $clientRequest = $clientRequestRepository->findBy(['team' => $team, 'activ' => true], ['createdAt' => 'DESC']);
            $title = $this->translator->trans(id: 'report.about.clientRequest', domain: 'bericht');
        }

        if (count($clientRequest) < 1) {
            return $this->redirectNoReport();
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

    #[Route(path: '/software', name: '_software')]
    public function reportSoftware(
        DompdfWrapper      $wrapper,
        Request            $request,
        CurrentTeamService $currentTeamService,
        SoftwareRepository $softwareRepository,
    )
    {
        $id = $request->get('id');
        $team = $currentTeamService->getCurrentTeam($this->getUser());

        if ($id) {
            $software = $softwareRepository->findBy(['id'=>$id]);
        } else {
            $software = $softwareRepository->findBy(['team' => $team, 'activ' => true], ['createdAt' => 'DESC']);
        }

        if (count($software) < 1) {
            return $this->redirectNoReport();
        }

        // Center Team authentication
        if ($team === null || $software[0]->getTeam() !== $team) {
            return $this->redirectToRoute('dashboard');
        }
        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('bericht/software.html.twig', [
            'daten' => $software,
            'titel' => $this->translator->trans(id: 'report.about.software', domain: 'bericht'),
            'team' => $team,
            'all' => $request->get('all'),
        ]);

        //Generate PDF File for Download
        $response = $wrapper->getStreamResponse(
            $html,
            $this->translator->trans('softwareConfig', domain: 'software') . '.pdf',
        );
        $response->send();
    }

    #[Route(path: '/tom', name: '_tom')]
    public function reportTom(
        DompdfWrapper      $wrapper,
        Request            $request,
        CurrentTeamService $currentTeamService,
        TomRepository      $tomRepository,
    )
    {

        $req = $request->get('id');
        $team = $currentTeamService->getCurrentTeam($this->getUser());

        if ($req) {
            $tom = $tomRepository->findBy(array('id' => $req));
        } else {
            $tom = $tomRepository->findBy(array('team' => $team, 'activ' => true));
        }

        if (count($tom) < 1) {
            return $this->redirectNoReport();
        }

        // Center Team authentication
        if ($team === null || $tom[0]->getTeam() !== $team) {
            return $this->redirectToRoute('dashboard');
        }

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('bericht/berichtTom.html.twig', [
            'datenAll' => $tom,
            'titel' => $this->translator->trans(id: 'tom.word', domain: 'bericht'),
            'team' => $team,
        ]);

        //Generate PDF File for Download
        $response = $wrapper->getStreamResponse(
            $html,
            $this->translator->trans(id: 'technicalAndOrganizationMeasures', domain: 'audit_tom') . '.pdf');
        $response->send();
    }

    #[Route(path: '/vvt', name: '_vvt')]
    public function reportVvt(
        DompdfWrapper      $wrapper,
        Request            $request,
        CurrentTeamService $currentTeamService,
        VVTRepository      $vvtRepository,
    )
    {
        ini_set('max_execution_time', '900');
        ini_set('memory_limit', '512M');

        $req = $request->get('id');
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        $doc = $this->translator->trans(id: 'processing.directory', domain: 'vvt');

        if ($req) {
            $vvt = $vvtRepository->findBy(array('id' => $req));
            $title = $this->translator->trans(id: 'processing.export', domain: 'vvt') . $vvt[0]->getName();
            $doc = $vvt[0]->getName();
        } else {
            $vvt = $vvtRepository->findBy(array('team' => $team, 'activ' => true));
            $title = $this->translator->trans(id: 'processing.directoryOf', domain: 'vvt') . $team->getName();
        }

        if (count($vvt) < 1) {
            return $this->redirectNoReport();
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

    #[Route(path: '/reports', name: '_reports')]
    public function reports(
        Request            $request,
        CurrentTeamService $currentTeamService,
        ReportRepository   $reportRepository,
    )
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        $users = $team->getMembers();

        $members = [];
        foreach ($users as $item) {
            $members[$item->getEmail()] = $item->getId();
        }

        $form = $this->createForm(ReportExportType::class, ['action' => $this->generateUrl('bericht_reports'), 'method' => 'GET']);
        $form->handleRequest($request);

        $title = $this->translator->trans(id: 'report.about.workReport', domain: 'bericht');

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
                return $this->redirectNoReport();
            }

            // Center Team authentication
            if ($team === null || $report[0]->getTeam() !== $team) {
                return $this->redirectToRoute('dashboard');
            }


            // Create a new Word document
            $phpWord = new PhpWord();
            $phpWord->addTitleStyle(1, ['bold' => true], ['spaceAfter' => 240]);
            $phpWord->addTitleStyle(2, ['bold' => true], ['spaceBefore' => 300]);
            $header = ['size' => 34, 'bold' => true];
            $secHeader = ['size' => 13, 'bold' => true];

            $title = $data['title'];

            $section = $phpWord->addSection();
            $section->addText($title, $header);

            foreach ($report as $item) {

                // Adding a software to the document...
                $section->addTitle($item->getDate()->format('d.m.Y'), 2);

                $table = $section->addTable();
                $table->addRow();
                $table->addCell(100 * 50)->addText($this->translator->trans(id: 'date', domain: 'general'));
                $table->addCell(100 * 50)->addText($item->getDate()->format('d.m.Y'));

                $table->addRow();
                $table->addCell()->addText($this->translator->trans(id: 'startTime', domain: 'general'));
                $table->addCell()->addText($item->getStart()->format('H:i'));

                $table->addRow();
                $table->addCell()->addText($this->translator->trans(id: 'endTime', domain: 'general'));
                $table->addCell()->addText($item->getEnd()->format('H:i'));

                $table->addRow();
                $table->addCell()->addText($this->translator->trans(id: 'worker', domain: 'report'));
                $table->addCell()->addText($item->getUser()->getEmail());

                $table->addRow();
                $table->addCell()->addText($this->translator->trans(id: 'work.onSight', domain: 'report'));
                $table->addCell()->addText($this->translator->trans(id: ($item->getOnSite() ? 'yes' : 'no'), domain: 'general'));

                $table->addRow();
                $table->addCell()->addText($this->translator->trans(id: 'description', domain: 'general'));
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

    private function redirectNoReport(): RedirectResponse
    {
        return $this->redirectToRoute(
            'bericht',
            [
                'snack' => $this->translator->trans(id: 'report.notAvailable', domain: 'bericht')
            ]
        );
    }
}
