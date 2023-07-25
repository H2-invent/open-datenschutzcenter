<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 15.05.2020
 * Time: 09:15
 */

namespace App\Controller;

use App\Entity\Kontakte;
use App\Form\Type\KontaktType;
use App\Repository\KontakteRepository;
use App\Repository\TeamRepository;
use App\Service\ApproveService;
use App\Service\CurrentTeamService;
use App\Service\DisableService;
use App\Service\SecurityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class KontaktController extends AbstractController
{


    public function __construct(private readonly TranslatorInterface $translator,
                                private EntityManagerInterface       $em,
    )
    {
    }

    #[Route(path: '/kontakt/neu', name: 'kontakt_new')]
    public function addKontakt(
        ValidatorInterface $validator,
        Request            $request,
        SecurityService    $securityService,
        CurrentTeamService $currentTeamService,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $kontakt = new Kontakte();
        $kontakt->setTeam($team);
        $kontakt->setActiv(1);
        $form = $this->createForm(KontaktType::class, $kontakt);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $errors = $validator->validate($data);
            if (count($errors) == 0) {
                $this->em->persist($data);
                $this->em->flush();
                return $this->redirectToRoute('kontakt');
            }
        }
        return $this->render('kontakt/edit.html.twig', [
            'kontakt' => $kontakt,
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => $this->translator->trans(id: 'contact.create', domain: 'kontakt'),
            'new' => true,
            'isEditable' => true,
        ]);
    }

    #[Route(path: '/kontakt/approve', name: 'kontakt_approve')]
    public function approve(
        Request            $request,
        SecurityService    $securityService,
        ApproveService     $approveService,
        CurrentTeamService $currentTeamService,
        KontakteRepository $contactRepository,
    ): Response
    {
        $user = $this->getUser();
        $team = $currentTeamService->getCurrentTeam($user);
        $kontakt = $contactRepository->find($request->get('id'));

        if ($securityService->teamDataCheck($kontakt, $team) && $securityService->adminCheck($user, $team)) {
            $approve = $approveService->approve($kontakt, $user);
            return $this->redirectToRoute('kontakt_edit', ['id' => $kontakt->getId(), 'snack' => $approve['snack']]);
        }

        // if security check fails
        return $this->redirectToRoute('kontakt');
    }

    #[Route(path: '/kontakt/disable', name: 'kontakt_disable')]
    public function disable(
        Request            $request,
        SecurityService    $securityService,
        DisableService     $disableService,
        CurrentTeamService $currentTeamService,
        KontakteRepository $contactRepository,
    ): Response
    {
        $user = $this->getUser();
        $team = $currentTeamService->getCurrentTeam($user);
        $kontakt = $contactRepository->find($request->get('id'));

        if ($securityService->teamDataCheck($kontakt, $team) && $securityService->adminCheck($user, $team) && !$kontakt->getApproved()) {
            $disableService->disable($kontakt, $user);
        }

        return $this->redirectToRoute('kontakt');
    }

    #[Route(path: '/kontakt/edit', name: 'kontakt_edit')]
    public function editKontakt(
        ValidatorInterface $validator,
        Request            $request,
        SecurityService    $securityService,
        CurrentTeamService $currentTeamService,
        KontakteRepository $contactRepository,
        TeamRepository     $teamRepository,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        $teamPath = $team ? $teamRepository->getPath($team) : null;
        $kontakt = $contactRepository->find($request->get('id'));
        if ($securityService->teamPathDataCheck($kontakt, $teamPath) === false) {
            return $this->redirectToRoute('kurse');
        }

        $isEditable = $kontakt->getTeam() === $team;
        $form = $this->createForm(KontaktType::class, $kontakt, ['disabled' => !$isEditable]);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid() && $isEditable) {
            $data = $form->getData();
            $errors = $validator->validate($data);
            if (count($errors) == 0) {
                $this->em->persist($data);
                $this->em->flush();
                return $this->redirectToRoute(
                    'kontakt_edit',
                    [
                        'id' => $kontakt->getId(),
                        'snack' => $this->translator->trans(id: 'save.successful', domain: 'general'),
                    ]
                );
            }
        }
        return $this->render('kontakt/edit.html.twig', [
            'form' => $form->createView(),
            'kontakt' => $kontakt,
            'errors' => $errors,
            'title' => $this->translator->trans(id: 'contact.create', domain: 'kontakt'),
            'snack' => $request->get('snack'),
            'isEditable' => $isEditable,
        ]);
    }

    #[Route(path: '/kontakt', name: 'kontakt')]
    public function index(
        SecurityService    $securityService,
        CurrentTeamService $currentTeamService,
        KontakteRepository $contactRepository,
        TeamRepository     $teamRepository,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $teamPath = $teamRepository->getPath($team);
        $kontakte = $contactRepository->findActiveByTeamPath($teamPath);

        return $this->render('kontakt/index.html.twig', [
            'kontakte' => $kontakte,
            'title' => $this->translator->trans(id: 'contact', domain: 'general'),
            'currentTeam' => $team,
        ]);
    }
}
