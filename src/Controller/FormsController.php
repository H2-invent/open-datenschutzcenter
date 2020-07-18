<?php

namespace App\Controller;

use App\Entity\Forms;
use App\Service\AssignService;
use App\Service\FormsService;
use App\Service\SecurityService;
use League\Flysystem\FilesystemInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FormsController extends AbstractController
{
    /**
     * @Route("/forms", name="forms")
     */
    public function indexForms(SecurityService $securityService)
    {
        $team = $this->getUser()->getTeam();
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $daten = $this->getDoctrine()->getRepository(Forms::class)->findBy(array('team' => $team, 'activ' => true));
        return $this->render('forms/index.html.twig', [
            'table' => $daten,
            'titel' => 'Formulare',
        ]);
    }

    /**
     * @Route("/forms/new", name="forms_new")
     */
    public function addForms(ValidatorInterface $validator, Request $request, FormsService $formsService, SecurityService $securityService)
    {
        $team = $this->getUser()->getTeam();
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $daten = $formsService->newForm($this->getUser());

        $form = $formsService->createForm($daten, $team);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $daten = $form->getData();
            $errors = $validator->validate($daten);
            if (count($errors) == 0) {

                $em->persist($daten);
                $em->flush();
                return $this->redirectToRoute('forms');
            }
        }
        return $this->render('forms/new.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'Formular erstellen/hochladen',
            'daten' => $daten,
            'activ' => $daten->getActiv()
        ]);
    }

    /**
     * @Route("/forms/edit", name="forms_edit")
     */
    public function EditFormulare(ValidatorInterface $validator, Request $request, SecurityService $securityService, FormsService $formsService, AssignService $assignService)
    {
        $team = $this->getUser()->getTeam();
        $forms = $this->getDoctrine()->getRepository(Forms::class)->find($request->get('id'));

        if ($securityService->teamDataCheck($forms, $team) === false) {
            return $this->redirectToRoute('forms');
        }

        $newForms = $formsService->cloneForms($forms, $this->getUser());
        $form = $formsService->createForm($newForms, $team);
        $form->handleRequest($request);
        $assign = $assignService->createForm($forms, $team);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $forms->setActiv(false);
            $forms->setStatus(4);
            $newForms = $form->getData();

            $errors = $validator->validate($newForms);
            if (count($errors) == 0) {

                $em->persist($newForms);
                $em->persist($forms);
                $em->flush();
                return $this->redirectToRoute('forms_edit', array('id' => $newForms->getId(), 'snack' => 'Erfolgreich gespeichert'));
            }
        }
        return $this->render('forms/edit.html.twig', [
            'form' => $form->createView(),
            'assignForm' => $assign->createView(),
            'errors' => $errors,
            'title' => 'Formular bearbeiten',
            'daten' => $forms,
            'activ' => $forms->getActiv(),
            'snack' => $request->get('snack')
        ]);
    }

    /**
     * @Route("/forms/download/{id}", name="forms_download_file", methods={"GET"})
     * @ParamConverter("forms", options={"mapping"={"id"="id"}})
     */
    public function downloadArticleReference(FilesystemInterface $formsFileSystem, Forms $forms, SecurityService $securityService, LoggerInterface $logger)
    {

        $stream = $formsFileSystem->read($forms->getUpload());

        $team = $this->getUser()->getTeam();
        if ($securityService->teamDataCheck($forms, $team) === false) {
            $message = ['typ' => 'DOWNLOAD', 'error' => true, 'hinweis' => 'Fehlerhafter download. User nicht berechtigt!', 'dokument' => $forms->getUpload(), 'user' => $this->getUser()->getUsername()];
            $logger->error($message['typ'], $message);
            return $this->redirectToRoute('dashboard');
        }

        $type = $formsFileSystem->getMimetype($forms->getUpload());
        $response = new Response($stream);
        $response->headers->set('Content-Type', $type);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $forms->getUpload())
        );

        $response->headers->set('Content-Disposition', $disposition);
        $message = ['typ' => 'DOWNLOAD', 'error' => false, 'hinweis' => 'Download erfolgreich', 'dokument' => $forms->getUpload(), 'user' => $this->getUser()->getUsername()];
        $logger->info($message['typ'], $message);
        return $response;
    }
}
