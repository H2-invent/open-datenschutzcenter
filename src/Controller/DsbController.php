<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\TeamRepository;
use App\Service\CurrentTeamService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class DsbController extends BaseController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private EntityManagerInterface       $em,
        private CurrentTeamService           $currentTeamService,
    ) {
    }

    #[Route(path: '/ext-dsb/change', name: 'dsb_change')]
    public function change(
        Request            $request,
        TeamRepository     $teamRepository,
        CurrentTeamService $currentTeamService,
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $team = $teamRepository->find($request->get('id'));

        if (in_array($team, $user->getTeamDsb()->toarray())) {
            $user->addTeam($team);
            $team->addAdmin($user);
            $user->setAkademieUser($team);
            $this->em->persist($user);
            $this->em->persist($team);
            $this->em->flush();
            $currentTeamService->switchToTeam(strval($team->getId()));
            $this->addSuccessMessage($this->translator->trans(id: 'team.change.successful', domain: 'dsb'));
            return $this->redirectToRoute(
                'dashboard',
            );
        }
        $this->addErrorMessage($this->translator->trans(id: 'team.change.error', domain: 'dsb'));
        return $this->redirectToRoute(
            'dashboard',
        );
    }

    #[Route(path: '/ext-dsb', name: 'dsb')]
    public function index(CurrentTeamService $currentTeamService): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $dsbTeams = $user->getTeamDsb();
        if (count($dsbTeams) < 1) {
            return $this->redirectToRoute('dashboard');
        }

        $currentTeam = $currentTeamService->getCurrentAdminTeam($user);

        return $this->render('dsb/index.html.twig', [
            'teams' => $dsbTeams,
            'currentTeam' => $currentTeam,
        ]);
    }
}
