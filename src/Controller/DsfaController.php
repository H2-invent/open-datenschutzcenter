<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\Type\VvtDsfaType;
use App\Repository\VVTDsfaRepository;
use App\Repository\VVTRepository;
use App\Service\AssignService;
use App\Service\CurrentTeamService;
use App\Service\SecurityService;
use App\Service\VVTService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DsfaController extends BaseController
{
    public function __construct(
        private TranslatorInterface $translator,
        private VVTDsfaRepository $repo,
        private  VVTRepository $VVTRepository,
        private EntityManagerInterface $em,
        private CurrentTeamService $currentTeamService,
        private VVTService $VVTService,
        private SecurityService $securityService)
    {
    }

    #[Route('/dsfa', name: 'dsfa')]
    public function index(): Response
    {
        return $this->render('dsfa/index.html.twig', [
            'title' => $this->translator->trans(id: 'dataProtectionImpactAssessment.word_plural', domain: 'vvt'),
            'dsfas' => $this->repo->findActiveByTeam($this->getTeam()),
        ]);
    }

    #[Route(path: '/dsfa/new', name: 'dsfa_new')]
    public function new(ValidatorInterface $validator, Request $request): Response
    {
        $team = $this->getTeam();
        $vvt = $this->VVTRepository->find($request->get('vvt'));
        if ($this->securityService->teamDataCheck($vvt, $team) === false) {
            return $this->redirectToRoute('vvt');
        }

        $dsfa = $this->VVTService->newDsfa($this->getUser(), $vvt);
        $form = $this->createForm(VvtDsfaType::class,$dsfa );
        $form->handleRequest($request);
        $errors = array();

        if ($form->isSubmitted() && $form->isValid()) {
            $dsfa = $form->getData();
            $errors = $validator->validate($dsfa);
            if (count($errors) == 0) {
                $this->em->persist($dsfa);
                $this->em->flush();
                $this->addSuccessMessage($this->translator->trans(id: 'dsfa.created', domain: 'vvt'));

                return $this->redirectToRoute('vvt_edit', [
                    'id' => $dsfa->getVvt()->getId(),
                ]);
            }
        }

        return $this->render('dsfa/edit.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => $this->translator->trans(id: 'dataPrivacyFollowUpEstimation.create', domain: 'vvt'),
            'dsfa' => $dsfa,
            'activ' => $dsfa->getActiv(),
        ]);
    }

    #[Route(path: '/dsfa/edit', name: 'dsfa_edit')]
    public function edit(ValidatorInterface $validator, Request $request, AssignService $assignService): Response
    {
        $team = $this->getTeam();
        $dsfa = $this->repo->find($request->get('dsfa'));
        $isEditable = $dsfa->getVvt()->getTeam() === $team;

        if ($this->securityService->teamDataCheck($dsfa->getVvt(), $team) === false) {
            return $this->redirectToRoute('vvt');
        }

        $newDsfa = $this->VVTService->cloneDsfa($dsfa, $this->getUser());
        $form = $this->createForm(VvtDsfaType::class, $newDsfa);
        $form->handleRequest($request);
        $assign = $assignService->createForm($dsfa, $team);
        $errors = array();

        if ($form->isSubmitted() && $form->isValid() && $dsfa->getActiv() && !$dsfa->getVvt()->getApproved()) {
            $dsfa->setActiv(false);
            $newDsfa = $form->getData();
            $errors = $validator->validate($newDsfa);

            if (count($errors) == 0) {
                $this->em->persist($newDsfa);
                $this->em->persist($dsfa);
                $this->em->flush();
                $this->addSuccessMessage($this->translator->trans(id: 'save.successful', domain: 'general'));

                return $this->redirectToRoute('dsfa_edit', [
                    'dsfa' => $newDsfa->getId(),
                ]);
            }
        }

        $this->setBackButton($this->generateUrl('vvt_edit', ['id' => $dsfa->getVvt()->getId()]));

        return $this->render('dsfa/edit.html.twig', [
            'form' => $form->createView(),
            'assignForm' => $assign->createView(),
            'errors' => $errors,
            'title' => $this->translator->trans(id: 'dataPrivacyFollowUpEstimation.edit', domain: 'vvt'),
            'dsfa' => $dsfa,
            'activ' => $dsfa->getActiv(),
            'isEditable' => $isEditable,
        ]);
    }

    private function getTeam(): ?Team
    {
        return $this->currentTeamService->getCurrentTeam($this->getUser());
    }
}
