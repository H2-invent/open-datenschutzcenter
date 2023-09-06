<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 15.05.2020
 * Time: 09:15
 */

namespace App\Controller;

use App\Entity\AuditTom;
use App\Form\Type\AuditTomType;
use App\Repository\AuditTomAbteilungRepository;
use App\Repository\AuditTomRepository;
use App\Repository\AuditTomStatusRepository;
use App\Repository\AuditTomZieleRepository;
use App\Service\AssignService;
use App\Service\CurrentTeamService;
use App\Service\SecurityService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/audit-tom', name: 'audit_tom')]
class AuditTomController extends AbstractController
{


    public function __construct(
        private readonly TranslatorInterface $translator,
        private EntityManagerInterface       $em,
    )
    {
    }

    #[Route(path: '/new', name: '_new')]
    public function addAuditTom(
        ValidatorInterface          $validator,
        Request                     $request,
        SecurityService             $securityService,
        CurrentTeamService          $currentTeamService,
        AuditTomStatusRepository    $auditTomStatusRepository,
        AuditTomZieleRepository     $auditTomZieleRepository,
        AuditTomAbteilungRepository $auditTomAbteilungRepository,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());

        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('audit_tom');
        }

        $today = new DateTime();
        $audit = new AuditTom();
        $audit->setTeam($team);
        $audit->setNummer('AUDIT-' . hexdec(uniqid()));
        $audit->setActiv(true);
        $audit->setCreatedAt($today);
        $audit->setUser($this->getUser());
        $status = $auditTomStatusRepository->findAll();
        $ziele = $auditTomZieleRepository->findByTeam($team);
        $abteilungen = $auditTomAbteilungRepository->findActiveByTeam($team);

        $form = $this->createForm(
            AuditTomType::class,
            $audit,
            [
                'abteilungen' => $abteilungen,
                'ziele' => $ziele,
                'status' => $status
            ]
        );
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $errors = $validator->validate($data);
            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($data);
                $em->flush();
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

    #[Route(path: '/clone', name: '_clone')]
    public function cloneAuditTom(
        SecurityService    $securityService,
        CurrentTeamService $currentTeamService,
        AuditTomRepository $auditTomRepository,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('audit_tom');
        }

        $today = new DateTime();
        $audit = $auditTomRepository->findAllByTeam(1);

        foreach ($audit as $data) {
            if ($data->getCreatedAt() > $team->getClonedAt()) {
                $newAudit = clone $data;
                $newAudit->setTeam($team);
                $newAudit->setCreatedAt($today);
                $this->em->persist($newAudit);
            }

        }

        //set ClonedAt Date to be able to update later newer versions
        $team->setclonedAt($today);

        $this->em->persist($team);
        $this->em->flush();

        return $this->redirectToRoute('audit_tom');

    }

    #[Route(path: '/edit', name: '_edit')]
    public function editAuditTom(
        ValidatorInterface          $validator,
        Request                     $request,
        SecurityService             $securityService,
        AssignService               $assignService,
        CurrentTeamService          $currentTeamService,
        AuditTomRepository          $auditTomRepository,
        AuditTomStatusRepository    $auditTomStatusRepository,
        AuditTomAbteilungRepository $auditTomAbteilungRepository,
        AuditTomZieleRepository     $auditTomZieleRepository,
    )
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        $audit = $auditTomRepository->find($request->get('tom'));

        if ($securityService->teamDataCheck($audit, $team) === false) {
            return $this->redirectToRoute('audit_tom');
        }

        $today = new DateTime();
        $status = $auditTomStatusRepository->findAll();
        $abteilungen = $auditTomAbteilungRepository->findActiveByTeam($team);
        $ziele = $auditTomZieleRepository->findByTeam($team);


        $allAudits = array_reverse($auditTomRepository->findAllByTeam($team));

        $mykey = 0;
        foreach ($allAudits as $key => $item) {
            if ($item === $audit) {
                $mykey = $key;
            }
        }
        try {
            $nextAudit = $allAudits[++$mykey];
        } catch (Exception $e) {
            $nextAudit = $allAudits[0];
        }


        $newAudit = clone $audit;
        $newAudit->setPrevious($audit);
        $newAudit->setCreatedAt($today);
        $newAudit->setUser($this->getUser());
        $newAudit->setTeam($team);
        $form = $this->createForm(
            AuditTomType::class,
            $newAudit,
            [
                'abteilungen' => $abteilungen,
                'ziele' => $ziele,
                'status' => $status
            ]
        );
        $form->remove('nummer');
        $form->handleRequest($request);
        $assign = $assignService->createForm($audit, $team);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid() && $audit->getActiv() === true) {

            $audit->setActiv(false);
            $newAudit = $form->getData();
            $errors = $validator->validate($newAudit);
            if (count($errors) == 0) {
                $this->em->persist($newAudit);
                $this->em->persist($audit);
                $this->em->flush();
                return $this->redirectToRoute(
                    'audit_tom_edit',
                    [
                        'tom' => $newAudit->getId(),
                        'snack' => $this->translator->trans(id: 'save.successful', domain: 'general'),
                    ]
                );
            }
        }
        return $this->render(
            'audit_tom/edit.html.twig',
            [
                'form' => $form->createView(),
                'assignForm' => $assign->createView(),
                'errors' => $errors,
                'title' => $this->translator->trans(id: 'auditQuestion.edit', domain: 'audit_tom'),
                'audit' => $audit,
                'activ' => $audit->getActiv(),
                'activNummer' => false,
                'nextAudit' => $nextAudit,
                'snack' => $request->get('snack')
            ]
        );
    }

    #[Route(path: '', name: '')]
    public function index(
        SecurityService    $securityService,
        CurrentTeamService $currentTeamService,
        AuditTomRepository $auditTomRepository,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        $audit = $auditTomRepository->findAllByTeam($team);

        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('audit_tom/index.html.twig', [
            'audit' => $audit,
            'currentTeam' => $team,
        ]);
    }
}
