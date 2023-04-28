<?php

namespace App\Controller;

use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class DsbController extends AbstractController
{


    public function __construct(private readonly TranslatorInterface $translator,
                                private EntityManagerInterface       $em,
    )
    {
    }

    #[Route(path: '/ext-dsb/change', name: 'dsb_change')]
    public function change(
        Request        $request,
        TeamRepository $teamRepository,
    ): Response
    {
        $team = $teamRepository->find($request->get('id'));

        if (in_array($team, $this->getUser()->getTeamDsb()->toarray())) {
            $user = $this->getUser();
            $user->addTeam($team);
            $team->addAdmin($user);
            $user->setAkademieUser($team);
            $this->em->persist($user);
            $this->em->persist($team);
            $this->em->flush();
            return $this->redirectToRoute(
                'dashboard',
                [
                    'snack' => $this->translator->trans(id: 'team.change.successful', domain: 'dsb'),
                ],
            );
        }
        return $this->redirectToRoute(
            'dashboard',
            [
                'snack' => $this->translator->trans(id: 'team.change.error', domain: 'dsb'),
            ],
        );
    }

    #[Route(path: '/ext-dsb', name: 'dsb')]
    public function index(): Response
    {
        $dsbTeams = $this->getUser()->getTeamDsb();
        if (count($dsbTeams) < 1) {
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('dsb/index.html.twig', [
            'teams' => $dsbTeams,
        ]);
    }
}
