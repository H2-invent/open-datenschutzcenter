<?php

namespace App\Controller;

use App\Form\Type\ReportType;
use App\Repository\ReportRepository;
use App\Service\CurrentTeamService;
use App\Service\ReportService;
use App\Service\SecurityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReportController extends AbstractController
{


    public function __construct(private readonly TranslatorInterface $translator,
                                private EntityManagerInterface       $em,
    )
    {
    }

    #[Route(path: '/report/new', name: 'report_new')]
    public function addReport(
        ValidatorInterface $validator,
        Request            $request,
        SecurityService    $securityService,
        ReportService      $reportService,
        CurrentTeamService $currentTeamService,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('report');
        }

        $report = $reportService->newReport($team);

        $form = $this->createForm(ReportType::class, $report, ['user' => $team->getMembers()]);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $errors = $validator->validate($data);
            if (count($errors) == 0) {
                $this->em->persist($data);
                $this->em->flush();
                return $this->redirectToRoute('report');
            }
        }
        return $this->render('report/new.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => $this->translator->trans(id: 'task.create', domain: 'report'),
            'report' => $report,
            'activ' => $report->getActiv(),
        ]);
    }

    #[Route(path: '/report/delete', name: 'report_delete')]
    public function deleteReport(
        Request            $request,
        SecurityService    $securityService,
        CurrentTeamService $currentTeamService,
        ReportRepository   $reportRepository,
    ): Response
    {
        $user = $this->getUser();
        $team = $currentTeamService->getCurrentTeam($user);
        $report = $reportRepository->find($request->get('id'));

        if ($securityService->teamDataCheck($report, $team) && $securityService->adminCheck($user, $team)) {
            $report->setActiv(2);

            $this->em->persist($report);
            $this->em->flush();
        }

        return $this->redirectToRoute('report');
    }

    #[Route(path: '/report/edit', name: 'report_edit')]
    public function editReport(
        ValidatorInterface $validator,
        Request            $request,
        SecurityService    $securityService,
        CurrentTeamService $currentTeamService,
        ReportRepository   $reportRepository,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        $report = $reportRepository->find($request->get('id'));

        if ($securityService->teamDataCheck($report, $team) === false) {
            return $this->redirectToRoute('report');
        }

        $form = $this->createForm(ReportType::class, $report, ['user' => $team->getMembers()]);
        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid() && $report->getUser() === $this->getUser() && !$report->getInvoice()) {

            $data = $form->getData();
            $errors = $validator->validate($data);
            if (count($errors) == 0) {
                $this->em->persist($data);
                $this->em->flush();
                return $this->redirectToRoute(
                    'report_edit',
                    [
                        'id' => $data->getId(),
                        'snack' => $this->translator->trans(id: 'save.successful', domain: 'general')
                    ],
                );
            }
        }
        return $this->render('report/edit.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => $this->translator->trans(id: 'work.edit', domain: 'report'),
            'report' => $report,
            'activ' => $report->getActiv(),
            'snack' => $request->get('snack'),
            'edit' => $request->get('edit'),
        ]);
    }

    #[Route(path: '/report', name: 'report')]
    public function index(
        SecurityService    $securityService,
        CurrentTeamService $currentTeamService,
        ReportRepository   $reportRepository,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        $report = $reportRepository->findActiveByTeam($team);

        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('report/index.html.twig', [
            'report' => $report,
            'currentTeam' => $team,
        ]);
    }

    #[Route(path: '/report/invoice', name: 'report_invoice')]
    public function invoiceReport(
        Request            $request,
        SecurityService    $securityService,
        CurrentTeamService $currentTeamService,
        ReportRepository   $reportRepository,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        $report = $reportRepository->find($request->get('id'));

        if ($securityService->teamDataCheck($report, $team) === true) {
            if ($report->getUser() === $this->getUser()) {
                $report->setInvoice(1);
            }

            $this->em->persist($report);
            $this->em->flush();
        }

        return $this->redirectToRoute('report');
    }
}
