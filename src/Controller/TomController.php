<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 15.05.2020
 * Time: 09:15
 */
namespace App\Controller;

use App\Entity\AuditTom;
use App\Entity\Tom;
use App\Form\Type\TomType;
use App\Repository\AuditTomRepository;
use App\Repository\TomRepository;
use App\Service\ApproveService;
use App\Service\DisableService;
use App\Service\SecurityService;
use App\Service\TomService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Service\CurrentTeamService;

class TomController extends AbstractController
{
    #[Route(path: '/tom', name: 'tom')]
    public function index(TomRepository $tomRepository,
                          SecurityService $securityService,
                          CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $tom = $tomRepository->findActiveByTeam($team);

        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('tom/index.html.twig', [
            'tom' => $tom,
            'currentTeam' => $team
        ]);
    }

    #[Route(path: '/tom/new', name: 'tom_new')]
    public function addAuditTom(ValidatorInterface $validator,
                                Request $request,
                                EntityManagerInterface $entityManager,
                                SecurityService $securityService,
                                TomService $tomService,
                                CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
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
                $entityManager->persist($data);
                $entityManager->flush();
                return $this->redirectToRoute('tom');
            }
        }
        return $this->render('tom/new.html.twig', [
            'currentTeam' => $team,
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'TOM erstellen',
            'tom' => $tom,
            'activ' => $tom->getActiv(),
            'activTitel' => true
        ]);
    }

    #[Route(path: '/tom/edit', name: 'tom_edit')]
    public function EditTom(ValidatorInterface $validator,
                            EntityManagerInterface $entityManager,
                            Request $request,
                            TomRepository $tomRepository,
                            SecurityService $securityService,
                            TomService $tomService,
                            CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $tom = $tomRepository->find($request->get('tom'));

        if ($securityService->teamDataCheck($tom, $team) === false) {
            return $this->redirectToRoute('tom');
        }

        $newTom = $tomService->cloneTom($tom, $this->getUser());

        $form = $this->createForm(TomType::class, $newTom);
        $form->remove('titel');
        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid() && $tom->getActiv() === 1 && !$tom->getApproved()) {

            $tom->setActiv(false);
            $newTom = $form->getData();
            $errors = $validator->validate($newTom);
            if (count($errors) == 0) {
                $entityManager->persist($newTom);
                $entityManager->persist($tom);
                $entityManager->flush();
                return $this->redirectToRoute('tom_edit', ['tom' => $newTom->getId(), 'snack' => 'Erfolgreich gepeichert']);
            }
        }
        return $this->render('tom/edit.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'TOM bearbeiten',
            'tom' => $tom,
            'activ' => $tom->getActiv(),
            'activTitel' => false,
            'snack' => $request->get('snack'),
            'currentTeam' => $team
        ]);
    }

    #[Route(path: '/tom/clone', name: 'tom_clone')]
    public function cloneTom(EntityManagerInterface $entityManager,
                             AuditTomRepository $auditRepository,
                             CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        if ($team === null) {
            return $this->redirectToRoute('dashboard');
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

    #[Route(path: '/tom/approve', name: 'tom_approve')]
    public function approve(Request $request,
                            TomRepository $tomRepository,
                            SecurityService $securityService,
                            ApproveService $approveService,
                            CurrentTeamService $currentTeamService)
    {
        $user = $this->getUser();
        $team = $currentTeamService->getTeamFromSession($user);
        $tom = $tomRepository->find($request->get('id'));

        if ($securityService->teamDataCheck($tom, $team) && $securityService->adminCheck($user, $team)) {
            $approve = $approveService->approve($tom, $user);
            return $this->redirectToRoute('tom_edit', ['tom' => $approve['data'], 'snack' => $approve['snack']]);
        }

        // if security check fails
        return $this->redirectToRoute('tom');
    }

    #[Route(path: '/tom/disable', name: 'tom_disable')]
    public function disable(Request $request,
                            SecurityService $securityService,
                            DisableService $disableService,
                            TomRepository $tomRepository,
                            CurrentTeamService $currentTeamService)
    {
        $user = $this->getUser();
        $team = $currentTeamService->getTeamFromSession($user);
        $tom = $tomRepository->find($request->get('id'));

        if ($securityService->teamDataCheck($tom, $team) && $securityService->adminCheck($user, $team)) {
            $disableService->disable($tom, $user);
        }

        return $this->redirectToRoute('tom');
    }
}
