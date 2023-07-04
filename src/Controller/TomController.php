<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 15.05.2020
 * Time: 09:15
 */

namespace App\Controller;

use App\Form\Type\TomType;
use App\Repository\AuditTomRepository;
use App\Repository\TomRepository;
use App\Service\ApproveService;
use App\Service\CurrentTeamService;
use App\Service\DisableService;
use App\Service\SecurityService;
use App\Service\TomService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TomController extends AbstractController
{


    public function __construct(private readonly TranslatorInterface $translator,
                                private EntityManagerInterface       $em,
    )
    {
    }

    #[Route(path: '/tom/new', name: 'tom_new')]
    public function addAuditTom(
        ValidatorInterface $validator,
        Request            $request,
        SecurityService    $securityService,
        TomService         $tomService,
        CurrentTeamService $currentTeamService,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('tom');
        }

        $tom = $tomService->newTom($team, $this->getUser());

        $form = $this->createForm(TomType::class, $tom);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $errors = $validator->validate($data);
            if (count($errors) == 0) {
                $this->em->persist($data);
                $this->em->flush();
                return $this->redirectToRoute('tom');
            }
        }
        return $this->render('tom/new.html.twig', [
            'currentTeam' => $team,
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => $this->translator->trans(id: 'tom.create', domain: 'tom'),
            'tom' => $tom,
            'activ' => $tom->getActiv(),
            'activTitel' => true
        ]);
    }

    #[Route(path: '/tom/approve', name: 'tom_approve')]
    public function approve(
        Request            $request,
        SecurityService    $securityService,
        ApproveService     $approveService,
        CurrentTeamService $currentTeamService,
        TomRepository      $tomRepository,
    ): Response
    {
        $user = $this->getUser();
        $team = $currentTeamService->getCurrentTeam($user);
        $tom = $tomRepository->find($request->get('id'));

        if ($securityService->teamDataCheck($tom, $team) && $securityService->adminCheck($user, $team)) {
            $approve = $approveService->approve($tom, $user);
            return $this->redirectToRoute('tom_edit', ['tom' => $approve['data'], 'snack' => $approve['snack']]);
        }

        // if security check fails
        return $this->redirectToRoute('tom');
    }

    #[Route(path: '/tom/clone', name: 'tom_clone')]
    public function cloneTom(
        Request            $request,
        CurrentTeamService $currentTeamService,
        AuditTomRepository $auditTomRepository,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        if ($team === null) {
            return $this->redirectToRoute('dashboard');
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

    #[Route(path: '/tom/disable', name: 'tom_disable')]
    public function disable(
        Request            $request,
        SecurityService    $securityService,
        DisableService     $disableService,
        CurrentTeamService $currentTeamService,
        TomRepository      $tomRepository,
    ): Response
    {
        $user = $this->getUser();
        $team = $currentTeamService->getCurrentTeam($user);
        $tom = $tomRepository->find($request->get('id'));

        if ($securityService->teamDataCheck($tom, $team) && $securityService->adminCheck($user, $team)) {
            $disableService->disable($tom, $user);
        }

        return $this->redirectToRoute('tom');
    }

    #[Route(path: '/tom/edit', name: 'tom_edit')]
    public function editTom(
        ValidatorInterface $validator,
        Request            $request,
        SecurityService    $securityService,
        TomService         $tomService,
        CurrentTeamService $currentTeamService,
        TomRepository      $tomRepository,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        $tom = $tomRepository->find($request->get('tom'));

        if ($securityService->teamDataCheck($tom, $team) === false) {
            return $this->redirectToRoute('tom');
        }

        $newTom = $tomService->cloneTom($tom, $this->getUser());

        $form = $this->createForm(TomType::class, $newTom);
        $form->remove('titel');
        $form->handleRequest($request);
        $errors = [];
        if ($form->isSubmitted() && $form->isValid() && $tom->getActiv() === 1 && !$tom->getApproved()) {

            $tom->setActiv(false);
            $newTom = $form->getData();
            $errors = $validator->validate($newTom);
            if (count($errors) == 0) {
                $this->em->persist($newTom);
                $this->em->persist($tom);
                $this->em->flush();
                return $this->redirectToRoute(
                    'tom_edit',
                    [
                        'tom' => $newTom->getId(),
                        'snack' => $this->translator->trans(id: 'save.successful', domain: 'general'),
                    ],
                );
            }
        }
        return $this->render('tom/edit.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => $this->translator->trans(id: 'tom.edit', domain: 'tom'),
            'tom' => $tom,
            'activ' => $tom->getActiv(),
            'activTitel' => false,
            'snack' => $request->get('snack'),
            'currentTeam' => $team
        ]);
    }

    #[Route(path: '/tom', name: 'tom')]
    public function index(
        SecurityService    $securityService,
        CurrentTeamService $currentTeamService,
        TomRepository      $tomRepository,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        $tom = $tomRepository->findActiveByTeam($team);

        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('tom/index.html.twig', [
            'tom' => $tom,
            'currentTeam' => $team
        ]);
    }
}
