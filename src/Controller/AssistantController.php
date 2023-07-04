<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Team;
use App\Form\Type\DatenweitergabeType;
use App\Service\CurrentTeamService;
use App\Service\AssistantService;
use App\Service\SecurityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/assistant', name: 'assistant')]
class AssistantController extends AbstractController
{
    #[Route('', name: '')]
    public function index(SecurityService $securityService,
                          CurrentTeamService $currentTeamService,
    ): RedirectResponse|Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());

        if (!$securityService->teamCheck($team)) {
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('assistant/index.html.twig', [
            'currentTeam' => $team,
        ]);
    }

    #[Route('/step/{step}', name: '_step')]
    public function step(int $step,
                         Request $request,
                         SecurityService $securityService,
                         CurrentTeamService $currentTeamService,
                         AssistantService $assistantService,
                         ValidatorInterface $validator,
                         EntityManagerInterface $entityManager,
    ): RedirectResponse|Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());

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
                $validator,
                $entityManager
            );
        }

        if ($step == $assistantService->getStepCount()) {
            $assistantService->clear();
            $this->addFlash(
                'success',
                'assistant.finished'
            );
        } else {
            $this->addFlash(
                'danger',
                'step.error'
            );
        }

        return $this->redirectToRoute('assistant');
    }

    #[Route('/cancel', name: '_cancel')]
    public function cancel(AssistantService $assistantService) : Response
    {
        $assistantService->clear();
        $this->addFlash(
            'info',
            'assistant.aborted'
        );
        return $this->redirectToRoute('assistant');
    }

    private function renderStep(int $step,
                               Team $team,
                               Request $request,
                               AssistantService $assistantService,
                               ValidatorInterface $validator,
                               EntityManagerInterface $entityManager,
    ): RedirectResponse|Response
    {
        $title = $assistantService->getPropertyForStep($step, AssistantService::PROPERTY_TITLE);
        $info = $assistantService->getPropertyForStep($step, AssistantService::PROPERTY_INFO);
        $newTitle = $assistantService->getPropertyForStep($step, AssistantService::PROPERTY_NEW);
        $type = $assistantService->getPropertyForStep($step, AssistantService::PROPERTY_TYPE);
        $skip = $assistantService->getPropertyForStep($step, AssistantService::PROPERTY_SKIP);
        $select = $assistantService->getSelectDataForStep($step, $team);
        $newItem = $assistantService->createElementForStep($step, $this->getUser(), $team);
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
                $assistantService->saveToSession(step: $step, data: $data->getId());
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

    #[Route('/select', name: '_select')]
    public function select(Request $request,
                                  SecurityService $securityService,
                                  CurrentTeamService $currentTeamService,
                                  AssistantService $assistantService,
    ) : Response
    {
        $team = $currentTeamService->getCurrentTeam($this->getUser());
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        $step = $assistantService->getStep();
        $selected = $request->get('assistant_select');

        if ($selected) {
            $assistantService->saveToSession(step: $step, data: $selected);
        } elseif (!$assistantService->getPropertyForStep($step, AssistantService::PROPERTY_SKIP)) {
            $this->addFlash(
                'danger',
                'assistant.noneSelected'
            );
            return $this->redirectToRoute('assistant_step', ['step' => $step]);
        }
        return $this->redirectToRoute('assistant_step', ['step' => $step + 1]);
    }
}
