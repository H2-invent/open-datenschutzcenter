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

use App\Entity\Team;
use App\Form\Type\TeamType;
use App\Repository\AkademieBuchungenRepository;
use App\Repository\AuditTomRepository;
use App\Repository\DatenweitergabeRepository;
use App\Repository\FormsRepository;
use App\Repository\KontakteRepository;
use App\Repository\LoeschkonzeptRepository;
use App\Repository\PoliciesRepository;
use App\Repository\SoftwareRepository;
use App\Repository\TaskRepository;
use App\Repository\TeamRepository;
use App\Repository\TomRepository;
use App\Repository\VVTDatenkategorieRepository;
use App\Repository\VVTDsfaRepository;
use App\Repository\VVTRepository;
use App\Service\CurrentTeamService;
use App\Service\SecurityService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends BaseController
{
    #[Route(path: '/', name: 'dashboard')]
    public function dashboard(Request                     $request,
                              CurrentTeamService          $currentTeamService,
                              SecurityService             $securityService,
                              TeamRepository              $teamRepository,
                              DatenweitergabeRepository   $transferRepository,
                              VVTRepository               $processRepository,
                              AuditTomRepository          $auditRepository,
                              VVTDsfaRepository           $impactAssessmentRepository,
                              FormsRepository             $formRepository,
                              PoliciesRepository          $policyRepository,
                              SoftwareRepository          $softwareRepository,
                              KontakteRepository          $contactRepository,
                              TomRepository               $tomRepository,
                              LoeschkonzeptRepository     $deletionConceptRepository,
                              VVTDatenkategorieRepository $dataCategoryRepository,
                              AkademieBuchungenRepository $bookingRepository,
                              TaskRepository              $taskRepository
    ): Response
    {
        // if no teams exist, redirect to first_run
        $allTeams = $teamRepository->findAll();
        if (sizeof($allTeams) === 0) {
            return $this->redirectToRoute('first_run');
        }

        // else get team for current user
        $user = $this->getUser();
        $currentTeam = $currentTeamService->getCurrentTeam($user);

        if ($currentTeam === null) {
            if ($securityService->superAdminCheck($this->getUser())) {
                return $this->redirectToRoute('manage_teams');
            } elseif ($this->getUser()->getAkademieUser() !== null) {
                return $this->redirectToRoute('akademie');
            } elseif (count($this->getUser()->getTeamDsb()) > 0) {
                return $this->redirectToRoute('dsb');
            } else {

                return $this->redirectToRoute('no_team');
            }
        }

        $audit = $auditRepository->findAllByTeam($currentTeam);
        $daten = $transferRepository->findActiveTransfersByTeam($currentTeam);
        $av = $transferRepository->findActiveOrderProcessingsByTeam($currentTeam);
        $processes = $processRepository->findActiveByTeam($currentTeam);
        $vvtDsfa = $impactAssessmentRepository->findActiveByTeam($currentTeam);
        $contacts = $contactRepository->findActiveByTeam($currentTeam);
        $tom = $tomRepository->findActiveByTeam($currentTeam);
        $forms = $formRepository->findPublicByTeam($currentTeam);
        $policies = $policyRepository->findPublicByTeam($currentTeam);
        $software = $softwareRepository->findActiveByTeam($currentTeam);
        $tasks = $taskRepository->findActiveAndOpenByTeam($currentTeam);
        $loeschkonzepte = $deletionConceptRepository->findByTeam($currentTeam);
        $vvtdatenkategorien = $dataCategoryRepository->findByTeam($currentTeam);
        $kritischeAudits = $auditRepository->findCriticalByTeam($currentTeam);
        $criticalProcesses = $processRepository->findCriticalByTeam($currentTeam);
        $openDsfa = $impactAssessmentRepository->findActiveAndOpenByTeam($currentTeam);
        $buchungen = $bookingRepository->findActiveByUser($user);

        $assignVvt = $processRepository->findActiveByTeamAndUser($currentTeam, $user);
        $assignAudit = $auditRepository->findActiveByTeamAndUser($currentTeam, $user);
        $assignDsfa = $impactAssessmentRepository->findActiveByTeamAndUser($currentTeam, $user);
        $assignDatenweitergabe = $transferRepository->findActiveByTeamAndUser($currentTeam, $user);
        $assignTasks = $taskRepository->findActiveByTeamAndUser($currentTeam, $user);

        return $this->render('dashboard/index.html.twig', [
            'currentTeam' => $currentTeam,
            'audit' => $audit,
            'daten' => $daten,
            'vvt' => $processes,
            'dsfa' => $vvtDsfa,
            'kontakte' => $contacts,
            'kAudit' => $kritischeAudits,
            'kVvt' => $criticalProcesses,
            'openDsfa' => $openDsfa,
            'tom' => $tom,
            'av' => $av,
            'assignDaten' => $assignDatenweitergabe,
            'assignVvt' => $assignVvt,
            'assignAudit' => $assignAudit,
            'assignDsfa' => $assignDsfa,
            'akademie' => $buchungen,
            'forms' => $forms,
            'policies' => $policies,
            'software' => $software,
            'assignTasks' => $assignTasks,
            'tasks' => $tasks,
            'loeschkonzepte' => $loeschkonzepte,
            'vvtdatenkategorien' => $vvtdatenkategorien,
            'tabsAssignments' => $this->getTabsAssignments(),
        ]);
    }

    #[Route(path: '/no_team', name: 'no_team')]
    public function noTeam(): Response
    {
        if ($this->getUser()) {
            if (count($this->getUser()->getTeams())) {
                return $this->redirectToRoute('dashboard');
            }
            if ($this->getUser()->getAkademieUser()) {
                return $this->redirectToRoute('akademie');
            }
            if (count($this->getUser()->getTeamDsb()) > 0) {
                return $this->redirectToRoute('dsb');
            }
        }
        return $this->render('dashboard/noteam.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route(path: '/no_team/create', name: 'no_team_create')]
    public function noTeamCreate(): Response
    {
        if (!$_ENV['APP_DEMO']) {
            return $this->redirectToRoute('dashboard');
        }

        $team = (new Team())->setActiv(true);
        $form = $this->createForm(TeamType::class, $team, [
            'action' => $this->generateUrl('team_create')
        ])
            ->remove('video')
            ->remove('externalLink');

        return $this->render('dashboard/noteamCreate.html.twig', [
            'user' => $this->getUser(),
            'form' => $form->createView(),
        ]);
    }

    private function getTabsAssignments(): array
    {
        return [
            'tasks' => ['image' => 'images/task.png'],
            'academy' => ['image' => 'images/online-learning.png'],
            'audit' => ['image' => 'images/pruefung.png'],
            'vvt' => ['image' => 'images/prozess.png'],
            'dsfa' => ['image' => 'images/dsta.png'],
            'dw' => ['image' => 'images/data.png'],
        ];
    }
}
