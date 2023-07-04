<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Question;
use App\Entity\Questionnaire;
use App\Entity\QuestionnaireQuestion;
use App\Entity\Team;
use App\Entity\User;
use App\Form\Type\Questionnaire\QuestionnaireType;
use App\Repository\QuestionnaireRepository;
use App\Service\CurrentTeamService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/questionnaire', name: 'questionnaire')]
class QuestionnaireController extends BaseController
{
    private static string $TEMPLATE_DIR = 'questionnaire/questionnaire/';

    public function __construct(
        private EntityManagerInterface $em,
        private CurrentTeamService     $currentTeamService,
        private TranslatorInterface $translator
    )
    {
    }

    #[Route(path: '/create', name: '_create', methods: ['POST', 'GET'])]
    public function create(
        Request $request,
    ): Response
    {
        $this->setBackButton($this->generateUrl('akademie_admin') . '#tab-questionnaire');
        $form = $this->createForm(QuestionnaireType::class);
        $error = false;

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $questionnaire = $this->handleForm($form);

            if ($questionnaire !== null) {
                return $this->redirectToRoute('question_create', ['id' => $questionnaire->getId()]);
            }

            $error = true;
        }

        return $this->render(
            self::$TEMPLATE_DIR . 'create.html.twig',
            [
                'form' => $form,
                'error' => $error,
                'title' => $this->translator->trans(id: 'questionnaire.create', domain: 'questionnaire'),
            ],
        );
    }

    #[Route(path: '/edit/{id}', name: '_edit', methods: ['GET', 'POST'])]
    #[ParamConverter('questionnaire', class: 'App\Entity\Questionnaire', options: ['mapping' => ['id' => 'id']])]
    public function edit(
        Questionnaire $questionnaire,
        Request       $request,
    ): Response
    {
        $this->setBackButton($this->generateUrl('akademie_admin') . '#tab-questionnaire');

        $form = $this->createForm(QuestionnaireType::class, $questionnaire);

        $form->handleRequest($request);

        if($form->isSubmitted()){
            $success = $this->handleForm($form);

            if($success){
                $this->addSuccessMessage($this->translator->trans(id: 'save.changesSuccessful', domain: 'general'));
                return $this->redirect($this->generateUrl('akademie_admin') . '#tab-questionnaire');
            }
        }

        return $this->render(
            self::$TEMPLATE_DIR . 'edit.html.twig',
            [
                'form' => $form,
                'success' => $success??true,
                'title' => $this->translator->trans(id: 'questionnaire.edit', domain: 'questionnaire'),
            ],
        );
    }

    #[Route(path: '/delete/{id}', name: '_delete', methods: ['GET', 'POST'])]
    #[ParamConverter('questionnaire', class: 'App\Entity\Questionnaire', options: ['mapping' => ['id' => 'id']])]
    public function delete(Questionnaire $questionnaire): RedirectResponse
    {
        foreach ($questionnaire->getParticipationAnswers() as $participationAnswer) {
            $this->em->remove($participationAnswer);
        }

        foreach ($questionnaire->getQuestionnaireQuestions() as $questionnaireQuestion) {
            foreach ($questionnaireQuestion->getQuestion()->getAnswers() as $answer) {
                $this->em->remove($answer);
            }

            $this->em->remove($questionnaireQuestion->getQuestion());
            $this->em->remove($questionnaireQuestion);
        }

        $this->em->remove($questionnaire);
        $this->em->flush();
        $this->addSuccessMessage($this->translator->trans(id: 'deleted', domain: 'general'));

        return $this->redirectToRoute('akademie_admin');
    }

    #[Route(path: '/details/{id}', name: '_details', methods: ['GET'])]
    #[ParamConverter('questionnaire', class: 'App\Entity\Questionnaire', options: ['mapping' => ['id' => 'id']])]
    public function details(Questionnaire $questionnaire): Response
    {
        $this->setBackButton($this->generateUrl('akademie_admin') . '#tab-questionnaire');

        return $this->render(
            self::$TEMPLATE_DIR . 'details.html.twig',
            [
                'questionnaire' => $questionnaire,
            ],
        );
    }

    #[Route(path: '/{questionnaireId}/add/question/{questionId}', name: '_add_question', methods: ['GET'])]
    #[ParamConverter('questionnaire', class: 'App\Entity\Questionnaire', options: ['mapping' => ['questionnaireId' => 'id']])]
    #[ParamConverter('question', class: 'App\Entity\Question', options: ['mapping' => ['questionId' => 'id']])]
    public function addQuestion(
        Question      $question,
        Questionnaire $questionnaire,
    ): RedirectResponse
    {
        $latestStep = 0;
        foreach ($questionnaire->getQuestionnaireQuestions() as $questionnaireQuestion) {
            if ($latestStep < $questionnaireQuestion->getStep()) {
                $latestStep = $questionnaireQuestion->getStep();
            }
        }

        $questionnaireQuestion = (new QuestionnaireQuestion())
            ->setQuestion($question)
            ->setQuestionnaire($questionnaire)
            ->setStep(++$latestStep);

        $this->em->persist($questionnaireQuestion);
        $this->em->flush();

        return $this->redirectToRoute('questionnaire_details', ['id' => $questionnaire->getId()]);
    }

    private function handleForm(FormInterface $form): ?Questionnaire
    {
        if (!$form->isValid()) {
            return null;
        }

        /** @var Questionnaire $questionnaire */
        $questionnaire = $form->getData();
        $questionnaire->setTeam($this->getCurrentTeam());

        $this->em->persist($questionnaire);
        $this->em->flush();

        return $questionnaire;
    }

    private function getCurrentTeam(): Team
    {
        /** @var User&UserInterface $user */
        $user = $this->getUser();

        return $this->currentTeamService->getCurrentTeam($user);
    }
}
