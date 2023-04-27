<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 15.05.2020
 * Time: 09:15
 */

namespace App\Controller;

use App\Entity\AuditTom;
use App\Entity\AuditTomAbteilung;
use App\Entity\AuditTomStatus;
use App\Entity\AuditTomZiele;
use App\Form\Type\AuditTomType;
use App\Repository\AuditTomAbteilungRepository;
use App\Repository\AuditTomRepository;
use App\Repository\AuditTomStatusRepository;
use App\Repository\AuditTomZieleRepository;
use App\Service\AssignService;
use App\Service\CurrentTeamService;
use App\Service\SecurityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuditTomController extends AbstractController
{
    #[Route(path: '/audit-tom', name: 'audit_tom')]
    public function index(SecurityService $securityService,
                          AuditTomRepository $auditRepository,
                          CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $audit = $auditRepository->findAllByTeam($team);

        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('audit_tom/index.html.twig', [
            'audit' => $audit,
            'currentTeam' => $team,
        ]);
    }

    #[Route(path: '/audit-tom/new', name: 'audit_tom_new')]
    public function addAuditTom(ValidatorInterface $validator,
                                Request $request,
                                EntityManagerInterface $entityManager,
                                AuditTomStatusRepository $auditStatusRepository,
                                AuditTomZieleRepository $auditGoalRepository,
                                AuditTomAbteilungRepository $departmentRepository,
                                SecurityService $securityService,
                                CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());

        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('audit_tom');
        }

        $today = new \DateTime();
        $audit = new AuditTom();
        $audit->setTeam($team);
        $audit->setNummer('AUDIT-' . hexdec(uniqid()));
        $audit->setActiv(true);
        $audit->setCreatedAt($today);
        $audit->setUser($this->getUser());
        $status = $auditStatusRepository->findAll();
        $goals = $auditGoalRepository->findByTeam($team);
        $departments = $departmentRepository->findAllByTeam($team);

        $form = $this->createForm(AuditTomType::class, $audit, ['abteilungen' => $departments, 'ziele' => $goals, 'status' => $status]);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $errors = $validator->validate($data);
            if (count($errors) == 0) {
                $entityManager->persist($data);
                $entityManager->flush();
                return $this->redirectToRoute('audit_tom');
            }
        }
        return $this->render('audit_tom/new.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'A-Frage erstellen',
            'audit' => $audit,
            'activNummer' => true,
            'activ' => $audit->getActiv()
        ]);
    }

    #[Route(path: '/audit-tom/edit', name: 'audit_tom_edit')]
    public function EditAuditTom(ValidatorInterface $validator,
                                 Request $request,
                                 EntityManagerInterface $entityManager,
                                 AuditTomStatusRepository $auditStatusRepository,
                                 AuditTomZieleRepository $auditGoalRepository,
                                 AuditTomAbteilungRepository $auditDepartmentRepository,
                                 AuditTomRepository $auditRepository,
                                 SecurityService $securityService,
                                 AssignService $assignService,
                                 CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $audit = $auditRepository->find($request->get('tom'));

        if ($securityService->teamDataCheck($audit, $team) === false) {
            return $this->redirectToRoute('audit_tom');
        }

        $today = new \DateTime();
        $status = $auditStatusRepository->findAll();
        $departments = $auditDepartmentRepository->findAllByTeam($team);
        $goals = $auditGoalRepository->findByTeam($team);


        $allAudits = array_reverse($auditRepository->findAllByTeam($team));

        $mykey = 0;
        foreach ($allAudits as $key=>$item) {
            if ($item === $audit) {
                $mykey = $key;
            }
        }
        try {
            $nextAudit = $allAudits[++$mykey];
        }
        catch (\Exception $e) {
            $nextAudit = $allAudits[0];
        }


        $newAudit = clone $audit;
        $newAudit->setPrevious($audit);
        $newAudit->setCreatedAt($today);
        $newAudit->setUser($this->getUser());
        $newAudit->setTeam($team);
        $form = $this->createForm(AuditTomType::class, $newAudit, ['abteilungen' => $departments, 'ziele' => $goals, 'status' => $status]);
        $form->remove('nummer');
        $form->handleRequest($request);
        $assign = $assignService->createForm($audit, $team);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid() && $audit->getActiv() === true) {

            $audit->setActiv(false);
            $newAudit = $form->getData();
            $errors = $validator->validate($newAudit);
            if (count($errors) == 0) {
                $entityManager->persist($newAudit);
                $entityManager->persist($audit);
                $entityManager->flush();
                return $this->redirectToRoute('audit_tom_edit', array('tom' => $newAudit->getId(), 'snack' => 'Erfolgreich gepeichert'));
            }
        }
        return $this->render('audit_tom/edit.html.twig', [
            'form' => $form->createView(),
            'assignForm' => $assign->createView(),
            'errors' => $errors,
            'title' => 'A-Frage bearbeiten',
            'audit' => $audit,
            'activ' => $audit->getActiv(),
            'activNummer' => false,
            'nextAudit' => $nextAudit,
            'snack' => $request->get('snack')
        ]);
    }

    #[Route(path: '/audit-tom/clone', name: 'audit_tom_clone')]
    public function CloneAuditTom(SecurityService $securityService,
                                  EntityManagerInterface $entityManager,
                                  AuditTomRepository $auditRepository,
                                  CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('audit_tom');
        }

        $today = new \DateTime();
        $audit = $auditRepository->findAllByTeam(1);

        foreach ($audit as $data) {
            if ($data->getCreatedAt() > $team->getClonedAt()) {
                $newAudit = clone $data;
                $newAudit->setTeam($team);
                $newAudit->setCreatedAt($today);
                $entityManager->persist($newAudit);
            }

        }

        //set ClonedAt Date to be able to update later newer versions
        $team->setclonedAt($today);

        $entityManager->persist($team);
        $entityManager->flush();

        return $this->redirectToRoute('audit_tom');

    }
}
