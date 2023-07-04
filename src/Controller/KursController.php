<?php

namespace App\Controller;

use App\Entity\AkademieKurse;
use App\Form\Type\KursAnmeldungType;
use App\Form\Type\KursType;
use App\Repository\AkademieKurseRepository;
use App\Service\AkademieService;
use App\Service\CurrentTeamService;
use App\Service\SecurityService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class KursController extends AbstractController
{


    public function __construct(private readonly TranslatorInterface $translator,
                                private EntityManagerInterface       $em,
    )
    {
    }

    #[Route(path: '/kurs/new', name: 'akademie_kurs_new')]
    public function addKurs(
        ValidatorInterface $validator,
        Request            $request,
        SecurityService    $securityService,
        CurrentTeamService $currentTeamService,
    ): Response
    {
        $user = $this->getUser();
        $team = $currentTeamService->getCurrentAdminTeam($user);

        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('akademie_admin');
        }

        $today = new DateTime();
        $daten = new AkademieKurse();
        $daten->addTeam($team);
        $daten->setCreatedAt($today);
        $daten->setActiv(true);
        $daten->setUser($user);

        $form = $this->createForm(KursType::class, $daten);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $daten = $form->getData();
            $errors = $validator->validate($daten);

            if (count($errors) == 0) {
                $this->em->persist($daten);
                $this->em->flush();

                return $this->redirectToRoute('kurs_anmelden', ['id' => $daten->getId()]);
            }
        }

        return $this->render('akademie/new.html.twig', [
            'currentTeam' => $team,
            'adminArea' => true,
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => $this->translator->trans(id: 'lesson.create', domain: 'kurs'),
        ]);
    }

    #[Route(path: '/kurs/edit', name: 'akademie_kurs_edit')]
    public function editKurs(
        ValidatorInterface      $validator,
        Request                 $request,
        SecurityService         $securityService,
        CurrentTeamService      $currentTeamService,
        AkademieKurseRepository $academyLessonRepository,
    ): Response
    {
        $user = $this->getUser();
        $team = $currentTeamService->getCurrentTeam($user);
        $kurs = $academyLessonRepository->find($request->get('id'));

        if ($securityService->teamArrayDataCheck($kurs, $team) === false) {
            return $this->redirectToRoute('akademie_admin');
        }

        if ($this->getUser() !== $kurs->getUser()) {
            return $this->redirectToRoute('akademie_admin');
        }

        $today = new DateTime();
        $kurs->setCreatedAt($today);

        $form = $this->createForm(KursType::class, $kurs);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $daten = $form->getData();
            $errors = $validator->validate($daten);
            if (count($errors) == 0) {
                $this->em->persist($daten);
                $this->em->flush();
                return $this->redirectToRoute('kurs_anmelden', ['id' => $daten->getId()]);
            }
        }
        return $this->render('akademie/new.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => $this->translator->trans(id: 'processing.create', domain: 'kurs'),
        ]);
    }

    #[Route(path: '/kurs/anmelden', name: 'kurs_anmelden')]
    public function kursAnmelden(
        Request                 $request,
        SecurityService         $securityService,
        AkademieService         $akademieService,
        CurrentTeamService      $currentTeamService,
        AkademieKurseRepository $academyLessonRepository,
    ): Response
    {
        $user = $this->getUser();
        $team = $currentTeamService->getCurrentTeam($user);
        $kurs = $academyLessonRepository->find($request->get('id'));

        if ($securityService->teamArrayDataCheck($kurs, $team) === false) {
            return $this->redirectToRoute('akademie_admin');
        }

        $daten = array();
        $daten['zugewiesen'] = new DateTime();
        $form = $this->createForm(KursAnmeldungType::class, $daten, ['user' => $team->getAkademieUsers()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $daten = $form->getData();
            $akademieService->addUser($kurs, $daten);

            return $this->redirectToRoute('akademie_admin');
        }
        return $this->render('akademie/new.html.twig', [
            'form' => $form->createView(),
            'title' => $this->translator->trans(id: 'lesson.members.assign', domain: 'kurs'),
        ]);
    }

    #[Route(path: '/kurs/deaktivieren', name: 'kurs_deaktivieren')]
    public function kursDeaktivieren(
        Request                 $request,
        SecurityService         $securityService,
        AkademieService         $akademieService,
        CurrentTeamService      $currentTeamService,
        AkademieKurseRepository $academyLessonRepository,
    ): Response
    {
        $user = $this->getUser();
        $team = $currentTeamService->getCurrentTeam($user);
        $kurs = $academyLessonRepository->find($request->get('id'));

        if (!$securityService->teamArrayDataCheck($kurs, $team)) {
            return $this->redirectToRoute('akademie_admin');
        }

        $akademieService->removeKurs($team, $kurs);

        return $this->redirectToRoute('akademie_admin');
    }
}
