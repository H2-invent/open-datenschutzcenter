<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 15.05.2020
 * Time: 09:15
 */

namespace App\Controller;

use App\Entity\Datenweitergabe;
use App\Repository\DatenweitergabeRepository;
use App\Service\ApproveService;
use App\Service\AssignService;
use App\Service\CurrentTeamService;
use App\Service\DatenweitergabeService;
use App\Service\DisableService;
use App\Service\SecurityService;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DatenweitergabeController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private EntityManagerInterface       $em,
    )
    {
    }

    #[Route(path: '/auftragsverarbeitung/new', name: 'auftragsverarbeitung_new')]
    public function addAuftragsverarbeitung(
        ValidatorInterface     $validator,
        Request                $request,
        SecurityService        $securityService,
        DatenweitergabeService $datenweitergabeService,
        CurrentTeamService     $currentTeamService,
    ): Response
    {
        set_time_limit(600);
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $daten = $datenweitergabeService->newDatenweitergabe($this->getUser(), 2);

        $form = $datenweitergabeService->createForm($daten, $team);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {

            $daten = $form->getData();
            foreach ($daten->getVerfahren() as $item) {
                $item->addDatenweitergaben($daten);
                $this->em->persist($item);
            }

            $errors = $validator->validate($daten);
            if (count($errors) == 0) {
                $this->em->persist($daten);
                $this->em->flush();
                return $this->redirectToRoute('auftragsverarbeitung');
            }
        }
        return $this->render('datenweitergabe/new.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => $this->translator->trans(id: 'requestProcessing.create', domain: 'datenweitergabe'),
            'daten' => $daten,
            'activNummer' => true,
            'activ' => $daten->getActiv()
        ]);
    }

    #[Route(path: '/datenweitergabe/new', name: 'datenweitergabe_new')]
    public function addDatenweitergabe(
        ValidatorInterface     $validator,
        Request                $request,
        DatenweitergabeService $datenweitergabeService,
        SecurityService        $securityService,
        CurrentTeamService     $currentTeamService,
    ): Response
    {
        set_time_limit(600);
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $daten = $datenweitergabeService->newDatenweitergabe($this->getUser(), 1);

        $form = $datenweitergabeService->createForm($daten, $team);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {

            $daten = $form->getData();
            foreach ($daten->getVerfahren() as $item) {
                $item->addDatenweitergaben($daten);
                $this->em->persist($item);
            }
            $errors = $validator->validate($daten);
            if (count($errors) == 0) {

                $this->em->persist($daten);
                $this->em->flush();
                return $this->redirectToRoute('datenweitergabe');
            }
        }
        return $this->render('datenweitergabe/new.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => $this->translator->trans(id: 'dataTransfer.create', domain: 'datenweitergabe'),
            'daten' => $daten,
            'activNummer' => true,
            'activ' => $daten->getActiv()
        ]);
    }

    #[Route(path: '/datenweitergabe/approve', name: 'datenweitergabe_approve')]
    public function approveDatenweitergabe(
        Request                   $request,
        SecurityService           $securityService,
        ApproveService            $approveService,
        CurrentTeamService        $currentTeamService,
        DatenweitergabeRepository $dataTransferRepository,
    ): Response
    {
        $user = $this->getUser();
        $team = $currentTeamService->getCurrentTeam($user);
        $daten = $dataTransferRepository->find($request->get('id'));

        if ($securityService->teamDataCheck($daten, $team) && $securityService->adminCheck($user, $team)) {
            $approve = $approveService->approve($daten, $this->getUser());
            if ($approve['clone'] === true) {
                $newDaten = $dataTransferRepository->find($approve['data']);


                foreach ($newDaten->getVerfahren() as $item) {
                    $item->addDatenweitergaben($newDaten);
                    $this->em->persist($item);
                }
                foreach ($newDaten->getSoftware() as $item) {
                    $item->addDatenweitergabe($newDaten);
                    $this->em->persist($item);
                }
                $this->em->flush();
            }
            return $this->redirectToRoute('datenweitergabe_edit', ['id' => $approve['data'], 'snack' => $approve['snack']]);
        }

        // if security check fails
        if ($daten->getArt() === 1) {
            return $this->redirectToRoute('datenweitergabe');
        }
        return $this->redirectToRoute('auftragsverarbeitung');
    }

    #[Route(path: '/datenweitergabe/disable', name: 'datenweitergabe_disable')]
    public function disableDatenweitergabe(
        Request                   $request,
        SecurityService           $securityService,
        DisableService            $disableService,
        CurrentTeamService        $currentTeamService,
        DatenweitergabeRepository $dataTransferRepository,
    ): Response
    {
        $user = $this->getUser();
        $team = $currentTeamService->getCurrentTeam($user);
        $daten = $dataTransferRepository->find($request->get('id'));

        if ($securityService->teamDataCheck($daten, $team) && $securityService->adminCheck($user, $team) && !$daten->getApproved()) {
            $disableService->disable($daten, $user);
        }

        if ($daten->getArt() === 1) {
            return $this->redirectToRoute('datenweitergabe');
        }
        return $this->redirectToRoute('auftragsverarbeitung');
    }

    #[Route(path: '/datenweitergabe/download/{id}', name: 'datenweitergabe_download_file', methods: ['GET'])]
    #[ParamConverter('datenweitergabe', options: ['mapping' => ['id' => 'id']])]
    public function downloadArticleReference(
        FilesystemOperator $datenFilesystem,
        Datenweitergabe    $datenweitergabe,
        SecurityService    $securityService,
        LoggerInterface    $logger,
        CurrentTeamService $currentTeamService,
    ): Response
    {
        $stream = $datenFilesystem->read($datenweitergabe->getUpload());
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        if ($securityService->teamDataCheck($datenweitergabe, $team) === false) {
            $logger->error(
                'DOWNLOAD',
                [
                    'typ' => 'DOWNLOAD',
                    'error' => true,
                    'hinweis' => 'Fehlerhafter download. User nicht berechtigt!',
                    'dokument' => $datenweitergabe->getUpload(),
                    'user' => $this->getUser()->getUsername()
                ]);
            return $this->redirectToRoute('dashboard');
        }

        $type = $datenFilesystem->getMimetype($datenweitergabe->getUpload());
        $response = new Response($stream);
        $response->headers->set('Content-Type', $type);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $datenweitergabe->getUpload())
        );

        $response->headers->set('Content-Disposition', $disposition);
        $logger->info(
            'DOWNLOAD',
            [
                'typ' => 'DOWNLOAD',
                'error' => false,
                'hinweis' => 'Download erfolgreich',
                'dokument' => $datenweitergabe->getUpload(),
                'user' => $this->getUser()->getUsername()
            ],
        );
        return $response;
    }

    #[Route(path: '/datenweitergabe/edit', name: 'datenweitergabe_edit')]
    public function editDatenweitergabe(
        ValidatorInterface        $validator,
        Request                   $request,
        SecurityService           $securityService,
        DatenweitergabeService    $datenweitergabeService,
        AssignService             $assignService,
        CurrentTeamService        $currentTeamService,
        DatenweitergabeRepository $dataTransferRepository,
    ): Response
    {
        set_time_limit(600);
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        $daten = $dataTransferRepository->find($request->get('id'));

        if ($securityService->teamDataCheck($daten, $team) === false) {
            if ($daten->getArt() === 1) {
                return $this->redirectToRoute('datenweitergabe');
            }
            return $this->redirectToRoute('auftragsverarbeitung');
        }

        $newDaten = $datenweitergabeService->cloneDatenweitergabe($daten, $this->getUser());
        $form = $datenweitergabeService->createForm($newDaten, $team);
        $form->remove('nummer');
        $form->handleRequest($request);
        $assign = $assignService->createForm($daten, $team);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid() && $daten->getActiv() && !$daten->getApproved()) {


            $daten->setActiv(false);
            $newDaten = $form->getData();

            foreach ($newDaten->getVerfahren() as $item) {
                $item->addDatenweitergaben($newDaten);
                $this->em->persist($item);
            }
            foreach ($newDaten->getSoftware() as $item) {
                $item->addDatenweitergabe($newDaten);
                $this->em->persist($item);
            }

            $errors = $validator->validate($newDaten);
            if (count($errors) == 0) {

                $this->em->persist($newDaten);
                $this->em->persist($daten);
                $this->em->flush();
                return $this->redirectToRoute(
                    'datenweitergabe_edit',
                    [
                        'id' => $newDaten->getId(),
                        'snack' => $this->translator->trans(id: 'save.successful', domain: 'general'),
                    ],
                );
            }
        }
        return $this->render('datenweitergabe/edit.html.twig', [
            'form' => $form->createView(),
            'assignForm' => $assign->createView(),
            'errors' => $errors,
            'title' => $this->translator->trans(id: 'dataTransfer.edit', domain: 'datenweitergabe'),
            'daten' => $daten,
            'activ' => $daten->getActiv(),
            'activNummer' => false,
            'snack' => $request->get('snack')
        ]);
    }

    #[Route(path: '/auftragsverarbeitung', name: 'auftragsverarbeitung')]
    public function indexAuftragsverarbeitung(
        SecurityService           $securityService,
        CurrentTeamService        $currentTeamService,
        DatenweitergabeRepository $dataTransferRepository,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $daten = $dataTransferRepository->findBy(array('team' => $team, 'activ' => true, 'art' => 2));
        return $this->render('datenweitergabe/indexAuftragsverarbeitung.html.twig', [
            'table' => $daten,
            'titel' => $this->translator->trans(id: 'dataTransfer.disclaimer', domain: 'datenweitergabe'),
            'currentTeam' => $team,
        ]);
    }

    #[Route(path: '/datenweitergabe', name: 'datenweitergabe')]
    public function indexDataTransfer(
        SecurityService           $securityService,
        CurrentTeamService        $currentTeamService,
        DatenweitergabeRepository $dataTransferRepository,
    ): Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $daten = $dataTransferRepository->findBy(array('team' => $team, 'activ' => true, 'art' => 1));
        return $this->render('datenweitergabe/index.html.twig', [
            'table' => $daten,
            'titel' => $this->translator->trans(id: 'dataTransfer.disclaimer', domain: 'datenweitergabe'),
            'currentTeam' => $team,
        ]);
    }
}
