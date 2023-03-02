<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\Type\DatenweitergabeType;
use App\Service\CurrentTeamService;
use App\Service\AssistantService;
use App\Service\SecurityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AssistantController extends AbstractController
{
    /**
     * @Route("/assistant", name="assistant")
     */
    public function index(SecurityService $securityService, CurrentTeamService $currentTeamService)
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());

        if (!$securityService->teamCheck($team)) {
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('assistant/index.html.twig', [
            'currentTeam' => $team,
        ]);
    }

    /**
     * @Route("/assistant/step/{step}", name="assistant_step")
     */
    public function step(int $step,
                         Request $request,
                         SecurityService $securityService,
                         CurrentTeamService $currentTeamService,
                         AssistantService $assistantService,
                         TranslatorInterface $translator,
                         ValidatorInterface $validator,
                         EntityManagerInterface $entityManager
    )
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());

        if (!$securityService->teamCheck($team)) {
            return $this->redirectToRoute('dashboard');
        }

        if ($step < $assistantService->getStepCount()) {
            $assistantService->setStep($step);
            return $this->renderStep(
                $step,
                $team,
                $request,
                $assistantService,
                $translator,
                $validator,
                $entityManager
            );
        }

        if ($step == $assistantService->getStepCount()) {
            $assistantService->clear();
            $this->addFlash(
                'success',
                'assistantFinished'
            );
        } else {
            $this->addFlash(
                'danger',
                'stepError'
            );
        }

        return $this->redirectToRoute('assistant');
    }

    /**
     * @Route("/assistant/cancel", name="assistant_cancel")
     */
    public function cancel(AssistantService $assistantService) : Response
    {
        $assistantService->clear();
        $this->addFlash(
            'info',
            'assistantAborted'
        );
        return $this->redirectToRoute('assistant');
    }

    public function renderStep(int $step,
                               Team $team,
                               Request $request,
                               AssistantService $assistantService,
                               TranslatorInterface $translator,
                               ValidatorInterface $validator,
                               EntityManagerInterface $entityManager)
    {
        $title = $translator->trans($assistantService->getTitleForStep($step));
        $info = $translator->trans($assistantService->getInfoForStep($step));
        $newTitle = $translator->trans($assistantService->getNewTitleForStep($step));
        $select = $assistantService->getSelectDataForStep($step, $team);
        $skip = $assistantService->getSkipForStep($step);
        $newItem = $assistantService->createElementForStep($step, $this->getUser(), $team);
        $type = $assistantService->getElementTypeForStep($step);
        $form = $assistantService->createForm($type, $newItem, $team);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $errors = $validator->validate($data);
            if (count($errors) == 0) {
                if ($type == DatenweitergabeType::class) {
                    foreach ($data->getVerfahren() as $item) {
                        $item->addDatenweitergaben($data);
                        $entityManager->persist($item);
                    }
                }
                $entityManager->persist($data);
                $entityManager->flush();
                $assistantService->setPropertyForStep($data->getId(), $step);
                return $this->redirectToRoute('assistant_step', ['step' => $step + 1]);
            }
        }
        return $this->render('assistant/step.html.twig', [
            'currentTeam' => $team,
            'title' => $title,
            'info' => $info,
            'newTitle' => $newTitle,
            'form' => $form->createView(),
            'errors' => $errors,
            'newItem' => $newItem,
            'select' => $select,
            'step' => $step,
            'skip' => $skip,
        ]);
    }

    /**
     * @Route("/assistant/contact/select", name="assistant_contact_select")
     */
    public function selectContact(Request $request,
                                  SecurityService $securityService,
                                  CurrentTeamService $currentTeamService,
                                  AssistantService $assistantService
    ) : Response
    {
        $team = $currentTeamService->getTeamFromSession($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $step = $assistantService->getStep();
        $contact = $request->get('assistant_select');
        $assistantService->setPropertyForStep($contact, $step);
        return $this->redirectToRoute('assistant_step', ['step' => $step + 1]);
    }
}
