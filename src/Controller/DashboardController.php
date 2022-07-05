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

class DashboardController extends AbstractController
{
    /**
     * @Route("/", name="dashboard")
     */
    public function dashboard(Request $request)
    {
        $teams = $this->getUser()->getTeams();
        if (sizeof($teams) === 0){
            return $this->redirectToRoute('first_run');
        }

        $team = $teams ? $teams[0] : null;
        if ($team === null && $this->getUser()->getAkademieUser() !== null) {
            return $this->redirectToRoute('akademie');
        } elseif ($team === null && count($dsbTeams = $this->getUser()->getTeamDsb()) > 0) {
            return $this->redirectToRoute('dsb');
        } elseif ($team === null && $this->getUser()->getAkademieUser() === null) {
            return $this->redirectToRoute('no_team');
        }

        $audit = $this->getDoctrine()->getRepository(AuditTom::class)->findAllByTeam($team);
        $daten = $this->getDoctrine()->getRepository(Datenweitergabe::class)->findBy(array('team' => $team, 'activ' => true, 'art' => 1));
        $av = $this->getDoctrine()->getRepository(Datenweitergabe::class)->findBy(array('team' => $team, 'activ' => true, 'art' => 2));
        $vvt = $this->getDoctrine()->getRepository(VVT::class)->findActivByTeam($team);
        $vvtDsfa = $this->getDoctrine()->getRepository(VVTDsfa::class)->findActivByTeam($team);
        $kontakte = $this->getDoctrine()->getRepository(Kontakte::class)->findActivByTeam($team);
        $tom = $this->getDoctrine()->getRepository(Tom::class)->findActivByTeam($team);
        $forms = $this->getDoctrine()->getRepository(Forms::class)->findPublicByTeam($team);
        $policies = $this->getDoctrine()->getRepository(Policies::class)->findPublicByTeam($team);
        $software = $this->getDoctrine()->getRepository(Software::class)->findActivByTeam($team);
        $tasks = $this->getDoctrine()->getRepository(Task::class)->findBy(['team' => $team, 'activ' => true, 'done' => false]);
        $loeschkonzepte = $this->getDoctrine()->getRepository(Loeschkonzept::class)->findByTeam($team);
        $vvtdatenkategorien = $this->getDoctrine()->getRepository(VVTDatenkategorie::class)->findByTeam($team);

        $qb = $this->getDoctrine()->getRepository(AuditTom::class)->createQueryBuilder('audit');
        $qb->andWhere('audit.team = :team')
            ->andWhere('audit.activ = 1')
            ->andWhere('audit.status = 5 OR audit.status = 6')
            ->orderBy('audit.createdAt', 'DESC')
            ->setParameter('team', $team);
        $query = $qb->getQuery();
        $kritischeAudits = $query->getResult();

        $qb = $this->getDoctrine()->getRepository(VVT::class)->createQueryBuilder('vvt');
        $qb->andWhere('vvt.team = :team')
            ->andWhere('vvt.activ = 1')
            ->andWhere('vvt.status = 3')
            ->orderBy('vvt.CreatedAt', 'DESC')
            ->setParameter('team', $team);
        $query = $qb->getQuery();
        $kritischeVvts = $query->getResult();

        $qb = $this->getDoctrine()->getRepository(VVTDsfa::class)->createQueryBuilder('dsfa');
        $qb->innerJoin('dsfa.vvt', 'vvt')
            ->andWhere('vvt.activ = 1')
            ->andWhere('dsfa.activ = 1')
            ->andWhere('dsfa.dsb IS NULL OR dsfa.ergebnis IS NULL')
            ->andWhere('vvt.team = :team')
            ->setParameter('team', $team);
        $query = $qb->getQuery();
        $openDsfa = $query->getResult();

        $assignVvt = $this->getUser()->getAssignedVvts()->toarray();
        $assignAudit = $this->getUser()->getAssignedAudits()->toarray();
        $assignDsfa = $this->getUser()->getAssignedDsfa()->toarray();
        $assignDatenweitergabe = $this->getUser()->getAssignedDatenweitergaben()->toarray();
        $assignTasks = $this->getDoctrine()->getRepository(Task::class)->findActivByUser($this->getUser());

        $buchungen = $this->getDoctrine()->getRepository(AkademieBuchungen::class)->findActivBuchungenByUser($this->getUser());

        return $this->render('dashboard/index.html.twig', [
            'team' => $team,
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
            if ($this->getUser()->getTeams()) {
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
