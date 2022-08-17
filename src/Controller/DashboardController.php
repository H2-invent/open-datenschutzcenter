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
use App\Entity\Datenweitergabe;
use App\Entity\Forms;
use App\Entity\Kontakte;
use App\Entity\Policies;
use App\Entity\Software;
use App\Entity\Task;
use App\Entity\Team;
use App\Entity\Tom;
use App\Entity\VVT;
use App\Entity\VVTDsfa;
use App\Entity\Loeschkonzept;
use App\Entity\VVTDatenkategorie;
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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\CurrentTeamService;

class DashboardController extends AbstractController
{
    /**
     * @Route("/", name="dashboard")
     * @param Request $request
     * @param CurrentTeamService $currentTeamService
     * @param TeamRepository $teamRepository
     * @param DatenweitergabeRepository $transferRepository
     * @param VVTRepository $processingActivityRepository
     * @param AuditTomRepository $auditRepository
     * @param VVTDsfaRepository $impactAssessmentRepository
     * @param FormsRepository $formRepository
     * @param PoliciesRepository $policyRepository
     * @param SoftwareRepository $softwareRepository
     * @param KontakteRepository $contactRepository
     * @param TomRepository $tomRepository
     * @param LoeschkonzeptRepository $deletionConceptRepository
     * @param VVTDatenkategorieRepository $dataCategoryRepository
     * @param AkademieBuchungenRepository $bookingRepository
     * @param TaskRepository $taskRepository
     * @return Response
     */
    public function dashboard(Request                     $request,
                              CurrentTeamService          $currentTeamService,
                              TeamRepository              $teamRepository,
                              DatenweitergabeRepository   $transferRepository,
                              VVTRepository               $processingActivityRepository,
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
    ) : Response
    {
        // if no teams exist, redirect to first_run
        $allTeams = $teamRepository->findAll();
        if (sizeof($allTeams) === 0){
            return $this->redirectToRoute('first_run');
        }

        // else get team for current user
        $user = $this->getUser();
        $currentTeam = $currentTeamService->getTeamFromSession($user);

        if ($currentTeam === null && $this->getUser()->getAkademieUser() !== null) {
            return $this->redirectToRoute('akademie');
        } elseif ($currentTeam === null && count($this->getUser()->getTeamDsb()) > 0) {
            return $this->redirectToRoute('dsb');
        } elseif ($currentTeam === null && $this->getUser()->getAkademieUser() === null) {
            return $this->redirectToRoute('no_team');
        }

        $audit = $auditRepository->findAllByTeam($currentTeam);
        $daten = $transferRepository->findActiveTransfersByTeam($currentTeam);
        $av = $transferRepository->findActiveOrderProcessingsByTeam($currentTeam);
        $vvt = $processingActivityRepository->findActiveByTeam($currentTeam);
        $vvtDsfa = $impactAssessmentRepository->findActiveByTeam($currentTeam);
        $kontakte = $contactRepository->findActiveByTeam($currentTeam);
        $tom = $tomRepository->findActiveByTeam($currentTeam);
        $forms = $formRepository->findPublicByTeam($currentTeam);
        $policies = $policyRepository->findPublicByTeam($currentTeam);
        $software = $softwareRepository->findActiveByTeam($currentTeam);
        $tasks = $taskRepository->findActiveAndOpenByTeam($currentTeam);
        $loeschkonzepte = $deletionConceptRepository->findByTeam($currentTeam);
        $vvtdatenkategorien = $dataCategoryRepository->findByTeam($currentTeam);
        $kritischeAudits = $auditRepository->findCriticalByTeam($currentTeam);
        $kritischeVvts = $processingActivityRepository->findCriticalByTeam($currentTeam);
        $openDsfa = $impactAssessmentRepository->findActiveAndOpenByTeam($currentTeam);
        $buchungen = $bookingRepository->findActiveByUser($user);

        $assignVvt = $processingActivityRepository->findActiveByTeamAndUser($currentTeam, $user);
        $assignAudit = $auditRepository->findActiveByTeamAndUser($currentTeam, $user);
        $assignDsfa = $impactAssessmentRepository->findActiveByTeamAndUser($currentTeam, $user);
        $assignDatenweitergabe = $transferRepository->findActiveByTeamAndUser($currentTeam, $user);
        $assignTasks = $taskRepository->findActiveByTeamAndUser($currentTeam, $user);

        return $this->render('dashboard/index.html.twig', [
            'currentTeam' => $currentTeam,
            'audit' => $audit,
            'daten' => $daten,
            'vvt' => $vvt,
            'dsfa' => $vvtDsfa,
            'kontakte' => $kontakte,
            'kAudit' => $kritischeAudits,
            'kVvt' => $kritischeVvts,
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
            'snack' => $request->get('snack'),
            'loeschkonzepte' => $loeschkonzepte,
            'vvtdatenkategorien' => $vvtdatenkategorien, 
            

        ]);
    }

    /**
     * @Route("/no_team", name="no_team")
     */
    public function noTeam()
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
}
