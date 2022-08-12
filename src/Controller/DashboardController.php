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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\CurrentTeamService;

class DashboardController extends AbstractController
{
    /**
     * @Route("/", name="dashboard")
     */
    public function dashboard(Request $request, CurrentTeamService $currentTeamService)
    {
        // if no teams exist, redirect to first_run
        $allTeams = $this->getDoctrine()->getRepository(Team::class)->findAll();
        if (sizeof($allTeams) === 0){
            return $this->redirectToRoute('first_run');
        }

        // else get team for current user
        $currentTeam = $currentTeamService->getTeamFromSession($this->getUser());

        if ($currentTeam === null && $this->getUser()->getAkademieUser() !== null) {
            return $this->redirectToRoute('akademie');
        } elseif ($currentTeam === null && count($dsbTeams = $this->getUser()->getTeamDsb()) > 0) {
            return $this->redirectToRoute('dsb');
        } elseif ($currentTeam === null && $this->getUser()->getAkademieUser() === null) {
            return $this->redirectToRoute('no_team');
        }

        $audit = $this->getDoctrine()->getRepository(AuditTom::class)->findAllByTeam($currentTeam);
        $daten = $this->getDoctrine()->getRepository(Datenweitergabe::class)->findBy(array('team' => $currentTeam, 'activ' => true, 'art' => 1));
        $av = $this->getDoctrine()->getRepository(Datenweitergabe::class)->findBy(array('team' => $currentTeam, 'activ' => true, 'art' => 2));
        $vvt = $this->getDoctrine()->getRepository(VVT::class)->findActiveByTeam($currentTeam);
        $vvtDsfa = $this->getDoctrine()->getRepository(VVTDsfa::class)->findActiveByTeam($currentTeam);
        $kontakte = $this->getDoctrine()->getRepository(Kontakte::class)->findActiveByTeam($currentTeam);
        $tom = $this->getDoctrine()->getRepository(Tom::class)->findActiveByTeam($currentTeam);
        $forms = $this->getDoctrine()->getRepository(Forms::class)->findPublicByTeam($currentTeam);
        $policies = $this->getDoctrine()->getRepository(Policies::class)->findPublicByTeam($currentTeam);
        $software = $this->getDoctrine()->getRepository(Software::class)->findActiveByTeam($currentTeam);
        $tasks = $this->getDoctrine()->getRepository(Task::class)->findBy(['team' => $currentTeam, 'activ' => true, 'done' => false]);
        $loeschkonzepte = $this->getDoctrine()->getRepository(Loeschkonzept::class)->findByTeam($currentTeam);
        $vvtdatenkategorien = $this->getDoctrine()->getRepository(VVTDatenkategorie::class)->findByTeam($currentTeam);

        $qb = $this->getDoctrine()->getRepository(AuditTom::class)->createQueryBuilder('audit');
        $qb->andWhere('audit.team = :team')
            ->andWhere('audit.activ = 1')
            ->andWhere('audit.status = 5 OR audit.status = 6')
            ->orderBy('audit.createdAt', 'DESC')
            ->setParameter('team', $currentTeam);
        $query = $qb->getQuery();
        $kritischeAudits = $query->getResult();

        $qb = $this->getDoctrine()->getRepository(VVT::class)->createQueryBuilder('vvt');
        $qb->andWhere('vvt.team = :team')
            ->andWhere('vvt.activ = 1')
            ->andWhere('vvt.status = 3')
            ->orderBy('vvt.CreatedAt', 'DESC')
            ->setParameter('team', $currentTeam);
        $query = $qb->getQuery();
        $kritischeVvts = $query->getResult();

        $qb = $this->getDoctrine()->getRepository(VVTDsfa::class)->createQueryBuilder('dsfa');
        $qb->innerJoin('dsfa.vvt', 'vvt')
            ->andWhere('vvt.activ = 1')
            ->andWhere('dsfa.activ = 1')
            ->andWhere('dsfa.dsb IS NULL OR dsfa.ergebnis IS NULL')
            ->andWhere('vvt.team = :team')
            ->setParameter('team', $currentTeam);
        $query = $qb->getQuery();
        $openDsfa = $query->getResult();

        $assignVvt = $this->getUser()->getAssignedVvts()->toarray();
        $assignAudit = $this->getUser()->getAssignedAudits()->toarray();
        $assignDsfa = $this->getUser()->getAssignedDsfa()->toarray();
        $assignDatenweitergabe = $this->getUser()->getAssignedDatenweitergaben()->toarray();
        $assignTasks = $this->getDoctrine()->getRepository(Task::class)->findActiveByUser($this->getUser());

        $buchungen = $this->getDoctrine()->getRepository(AkademieBuchungen::class)->findActivBuchungenByUser($this->getUser());

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
