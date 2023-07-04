<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 15.05.2020
 * Time: 09:15
 */

namespace App\Controller;

use App\Repository\VorfallRepository;
use App\Service\ApproveService;
use App\Service\AssignService;
use App\Service\CurrentTeamService;
use App\Service\SecurityService;
use App\Service\VorfallService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class VorfallController extends AbstractController
{


    public function __construct(private readonly TranslatorInterface $translator,
                                private EntityManagerInterface       $em,
    )
    {
    }

    #[Route(path: '/vorfall/new', name: 'vorfall_new')]
    public function addVorfall(
        ValidatorInterface $validator,
        Request            $request,
        SecurityService    $securityService,
        VorfallService     $vorfallService,
        CurrentTeamService $currentTeamService,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $vorfall = $vorfallService->newVorfall($team, $this->getUser());

        $form = $vorfallService->createForm($vorfall, $team);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $errors = $validator->validate($data);
            if (count($errors) == 0) {
                $this->em->persist($data);
                $this->em->flush();
                return $this->redirectToRoute('vorfall');
            }
        }
        return $this->render('vorfall/new.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => $this->translator->trans(id: 'incident.register', domain: 'vorfall'),
            'vorfall' => $vorfall,
            'activ' => $vorfall->getActiv()
        ]);
    }

    #[Route(path: '/vorfall/approve', name: 'vorfall_approve')]
    public function approve(
        Request            $request,
        SecurityService    $securityService,
        ApproveService     $approveService,
        CurrentTeamService $currentTeamService,
        VorfallRepository  $incidentRepository,
    ): Response
    {
        $user = $this->getUser();
        $team = $currentTeamService->getCurrentTeam($user);
        $vorfall = $incidentRepository->find($request->get('id'));

        if ($securityService->teamDataCheck($vorfall, $team) && $securityService->adminCheck($user, $team)) {
            $approve = $approveService->approve($vorfall, $user);
            return $this->redirectToRoute('vorfall_edit', ['id' => $approve['data'], 'snack' => $approve['snack']]);
        }

        // if security check fails
        return $this->redirectToRoute('vvt');
    }

    #[Route(path: '/vorfall/edit', name: 'vorfall_edit')]
    public function editVorfall(
        ValidatorInterface $validator,
        Request            $request,
        SecurityService    $securityService,
        VorfallService     $vorfallService,
        AssignService      $assignService,
        CurrentTeamService $currentTeamService,
        VorfallRepository  $incidentRepository,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        $vorgang = $incidentRepository->find($request->get('id'));

        if ($securityService->teamDataCheck($vorgang, $team) === false) {
            return $this->redirectToRoute('vorfall');
        }

        $newVorgang = $vorfallService->cloneVorfall($vorgang, $this->getUser());

        $form = $vorfallService->createForm($newVorgang, $team);
        $form->handleRequest($request);
        $assign = $assignService->createForm($vorgang, $team);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid() && $vorgang->getActiv() === true) {

            $vorgang->setActiv(false);
            $newVorgang = $form->getData();
            $errors = $validator->validate($newVorgang);
            if (count($errors) == 0) {
                $this->em->persist($newVorgang);
                $this->em->persist($vorgang);
                $this->em->flush();
                return $this->redirectToRoute(
                    'vorfall_edit',
                    [
                        'id' => $newVorgang->getId(),
                        'snack' => $this->translator->trans(id: 'save.successful', domain: 'general'),
                    ],
                );
            }
        }
        return $this->render('vorfall/edit.html.twig', [
            'form' => $form->createView(),
            'assignForm' => $assign->createView(),
            'errors' => $errors,
            'title' => $this->translator->trans(id: 'incident.edit', domain: 'vorfall'),
            'vorfall' => $vorgang,
            'activ' => $vorgang->getActiv(),
            'snack' => $request->get('snack')
        ]);
    }

    #[Route(path: '/vorfall', name: 'vorfall')]
    public function index(
        SecurityService    $securityService,
        CurrentTeamService $currentTeamService,
        VorfallRepository  $incidentRepository,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());

        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $vorfall = $incidentRepository->findAllByTeam($team);

        return $this->render('vorfall/index.html.twig', [
            'vorfall' => $vorfall,
            'currentTeam' => $team,
        ]);
    }
}
