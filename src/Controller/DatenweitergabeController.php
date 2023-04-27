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
use League\Flysystem\FilesystemInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DatenweitergabeController extends AbstractController
{
    #[Route(path: '/datenweitergabe', name: 'datenweitergabe')]
    public function indexDatenweitergabe(DatenweitergabeRepository $repository,
                                         SecurityService $securityService,
                                         CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $daten = $repository->findBy(array('team' => $team, 'activ' => true, 'art' => 1));
        return $this->render('datenweitergabe/index.html.twig', [
            'table' => $daten,
            'titel' => 'Alle Datenweitergaben nach DSGVO Art. 30(1)',
            'currentTeam' => $team,
        ]);
    }

    #[Route(path: '/auftragsverarbeitung', name: 'auftragsverarbeitung')]
    public function indexAuftragsverarbeitung(DatenweitergabeRepository $repository,
                                              SecurityService $securityService,
                                              CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $daten = $repository->findBy(array('team' => $team, 'activ' => true, 'art' => 2));
        return $this->render('datenweitergabe/indexAuftragsverarbeitung.html.twig', [
            'table' => $daten,
            'titel' => 'Verarbeitungstätigkeiten nach DSGVO Art. 30(2)',
            'currentTeam' => $team,
        ]);
    }

    #[Route(path: '/datenweitergabe/new', name: 'datenweitergabe_new')]
    public function addDatenweitergabe(ValidatorInterface $validator,
                                       Request $request,
                                       EntityManagerInterface $entityManager,
                                       DatenweitergabeService $datenweitergabeService,
                                       SecurityService $securityService,
                                       CurrentTeamService $currentTeamService)
    {
        set_time_limit(600);
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $daten = $datenweitergabeService->newDatenweitergabe($this->getUser(), 1, 'DW-');

        $form = $datenweitergabeService->createForm($daten, $team);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $daten = $form->getData();
            foreach ($daten->getVerfahren() as $item) {
                $item->addDatenweitergaben($daten);
                $entityManager->persist($item);
            }
            $errors = $validator->validate($daten);
            if (count($errors) == 0) {

                $entityManager->persist($daten);
                $entityManager->flush();
                return $this->redirectToRoute('datenweitergabe');
            }
        }
        return $this->render('datenweitergabe/new.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'Datenweitergabe erstellen',
            'daten' => $daten,
            'activNummer' => true,
            'activ' => $daten->getActiv()
        ]);
    }

    #[Route(path: '/auftragsverarbeitung/new', name: 'auftragsverarbeitung_new')]
    public function addAuftragsverarbeitung(ValidatorInterface $validator,
                                            EntityManagerInterface $entityManager,
                                            DatenweitergabeService $datenweitergabeService,
                                            Request $request,
                                            SecurityService $securityService,
                                            CurrentTeamService $currentTeamService)
    {
        set_time_limit(600);
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $daten = $datenweitergabeService->newDatenweitergabe($this->getUser(), 2, 'AVV-');

        $form = $datenweitergabeService->createForm($daten, $team);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $daten = $form->getData();
            foreach ($daten->getVerfahren() as $item) {
                $item->addDatenweitergaben($daten);
                $entityManager->persist($item);
            }

            $errors = $validator->validate($daten);
            if (count($errors) == 0) {
                $entityManager->persist($daten);
                $entityManager->flush();
                return $this->redirectToRoute('auftragsverarbeitung');
            }
        }
        return $this->render('datenweitergabe/new.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'Auftragsverarbeitung erstellen',
            'daten' => $daten,
            'activNummer' => true,
            'activ' => $daten->getActiv()
        ]);
    }

    #[Route(path: '/datenweitergabe/edit', name: 'datenweitergabe_edit')]
    public function EditDatenweitergabe(ValidatorInterface $validator,
                                        Request $request,
                                        EntityManagerInterface $entityManager,
                                        DatenweitergabeRepository $repository,
                                        SecurityService $securityService,
                                        DatenweitergabeService $datenweitergabeService,
                                        AssignService $assignService,
                                        CurrentTeamService $currentTeamService)
    {
        set_time_limit(600);
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        $daten = $repository->find($request->get('id'));

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
                $entityManager->persist($item);
            }
            foreach ($newDaten->getSoftware() as $item) {
                $item->addDatenweitergabe($newDaten);
                $entityManager->persist($item);
            }

            $errors = $validator->validate($newDaten);
            if (count($errors) == 0) {

                $entityManager->persist($newDaten);
                $entityManager->persist($daten);
                $entityManager->flush();
                return $this->redirectToRoute('datenweitergabe_edit', array('id' => $newDaten->getId(), 'snack' => 'Erfolgreich gespeichert'));
            }
        }
        return $this->render('datenweitergabe/edit.html.twig', [
            'form' => $form->createView(),
            'assignForm' => $assign->createView(),
            'errors' => $errors,
            'title' => 'Datenweitergabe bearbeiten',
            'daten' => $daten,
            'activ' => $daten->getActiv(),
            'activNummer' => false,
            'snack' => $request->get('snack')
        ]);
    }

    #[Route(path: '/datenweitergabe/download/{id}', name: 'datenweitergabe_download_file', methods: ['GET'])]
    #[ParamConverter('datenweitergabe', options: ['mapping' => ['id' => 'id']])]
    public function downloadArticleReference(FilesystemInterface $datenFileSystem, Datenweitergabe $datenweitergabe, SecurityService $securityService, LoggerInterface $logger, CurrentTeamService $currentTeamService)
    {
        $stream = $datenFileSystem->read($datenweitergabe->getUpload());
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        if ($securityService->teamDataCheck($datenweitergabe, $team) === false) {
            $message = ['typ' => 'DOWNLOAD', 'error' => true, 'hinweis' => 'Fehlerhafter download. User nicht berechtigt!', 'dokument' => $datenweitergabe->getUpload(), 'user' => $this->getUser()->getUsername()];
            $logger->error($message['typ'], $message);
            return $this->redirectToRoute('dashboard');
        }

        $type = $datenFileSystem->getMimetype($datenweitergabe->getUpload());
        $response = new Response($stream);
        $response->headers->set('Content-Type', $type);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $datenweitergabe->getUpload())
        );

        $response->headers->set('Content-Disposition', $disposition);
        $message = ['typ' => 'DOWNLOAD', 'error' => false, 'hinweis' => 'Download erfolgreich', 'dokument' => $datenweitergabe->getUpload(), 'user' => $this->getUser()->getUsername()];
        $logger->info($message['typ'], $message);
        return $response;
    }

    #[Route(path: '/datenweitergabe/approve', name: 'datenweitergabe_approve')]
    public function approveDatenweitergabe(Request $request,
                                           EntityManagerInterface $entityManager,
                                           DatenweitergabeRepository $repository,
                                           SecurityService $securityService,
                                           ApproveService $approveService,
                                           CurrentTeamService $currentTeamService)
    {
        $user = $this->getUser();
        $team = $currentTeamService->getTeamFromSession($user);
        $daten = $repository->find($request->get('id'));

        if ($securityService->teamDataCheck($daten, $team) && $securityService->adminCheck($user, $team)) {
            $approve = $approveService->approve($daten, $this->getUser());
            if ($approve['clone'] === true) {
                $newDaten = $repository->find($approve['data']);

                foreach ($newDaten->getVerfahren() as $item) {
                    $item->addDatenweitergaben($newDaten);
                    $entityManager->persist($item);
                }
                foreach ($newDaten->getSoftware() as $item) {
                    $item->addDatenweitergabe($newDaten);
                    $entityManager->persist($item);
                }
                $entityManager->flush();
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
    public function disableDatenweitergabe(Request $request,
                                           SecurityService $securityService,
                                           DisableService $disableService,
                                           DatenweitergabeRepository $repository,
                                           CurrentTeamService $currentTeamService)
    {
        $user = $this->getUser();
        $team = $currentTeamService->getTeamFromSession($user);
        $daten = $repository->find($request->get('id'));

        if ($securityService->teamDataCheck($daten, $team) && $securityService->adminCheck($user, $team) && !$daten->getApproved()) {
            $disableService->disable($daten, $user);
        }

        if ($daten->getArt() === 1) {
            return $this->redirectToRoute('datenweitergabe');
        }
        return $this->redirectToRoute('auftragsverarbeitung');
    }
}
