<?php
declare(strict_types=1);

namespace App\Controller;

use App\DataTypes\ParticipationStateTypes;
use App\DataTypes\QuestionTypes;
use App\Entity\Participation;
use App\Entity\ParticipationAnswer;
use App\Entity\Question;
use App\Entity\Questionnaire;
use App\Entity\QuestionnaireQuestion;
use App\Form\Type\Questionnaire\Question\DynamicQuestionType;
use App\Repository\QuestionnaireQuestionRepository;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: 'participation-question/{id}', name: 'participation_question')]
#[ParamConverter(data: 'participation', class: Participation::class, options: ['mapping' => ['id' => 'id']])]
class ParticipationQuestionController extends BaseController
{
    private static string $TEMPLATE_DIR = 'questionnaire/participationQuestion/';

    public function __construct(
        private QuestionnaireQuestionRepository $questionnaireQuestionRepository,
        private QuestionRepository              $questionRepository,
        private EntityManagerInterface          $em,
    )
    {
    }

    #[Route(path: '', name: '', methods: ['GET', 'POST'])]
    public function question(Participation $participation, Request $request): Response
    {
        # region validate
        $error = $this->getError($participation);

        if ($error !== null) {
            return $error;
        }
        # endregion validate

        # region handle_previous_question
        $previousForm = $this->getPreviousForm($request);

        if ($previousForm !== null && $previousForm->isSubmitted()) {
            $this->handleForm($previousForm, $participation);
            $this->em->refresh($participation);
        }
        # endregion handle_previous_question

        # region get_current_question
        $question = $this->getCurrentQuestion($previousForm, $participation);
        # endregion get_current_question

        # region finish_if_last_question
        if ($question === null) {
            return $this->redirectToRoute('participation_finish', ['id' => $participation->getId()]);
        }
        # endregion finish_if_last_question

        return $this->render(
            view: self::$TEMPLATE_DIR . 'question.html.twig',
            parameters: ['form' => $this->createForm(DynamicQuestionType::class, options: ['question' => $question]),
                'question' => $question, 'participation' => $participation],
        );
    }

    private function getPreviousForm(Request $request): ?FormInterface
    {
        if($request->get('dynamic_question') === null){
            return null;
        }

        $questionId = array_key_first($request->get('dynamic_question'));
        $previousQuestion = $this->questionRepository->find($questionId);

        $form = $this->createForm(DynamicQuestionType::class, options: ['question' => $previousQuestion]);
        $form->handleRequest($request);

        return $form;
    }

    private function getCurrentQuestion(?FormInterface $form, Participation $participation): ?Question
    {
        $sortedQuestions = $this->getSortedQuestions($participation->getQuestionnaire());

        if ($form === null) {
            return $sortedQuestions[0]->getQuestion();
        } else {
            $latestParticipationAnswer = $this->getLatestParticipationAnswer($participation);
            $previousQuestionnaireQuestion = $this->getPreviousQuestionnaireQuestion($sortedQuestions, $latestParticipationAnswer);
            if($previousQuestionnaireQuestion === array_reverse($sortedQuestions)[0]){
                return null;
            }

            return $this->getNextQuestionnaireQuestionByQuestionnaireQuestion($previousQuestionnaireQuestion)->getQuestion();
        }
    }

    private function getLatestParticipationAnswer(Participation $participation): ?ParticipationAnswer
    {
        foreach ($participation->getParticipationAnswers() as $participationAnswer) {
            if (
                !isset($latestAnswer)
                || $participationAnswer->getUpdatedAt()->format('U') > $latestAnswer->getUpdatedAt()->format('U')
            ) {
                $latestAnswer = $participationAnswer;
            }
        }

        return $latestAnswer ?? null;
    }

    private
    function getError(Participation $participation): ?Response
    {
        if ($participation->getAcademyBilling()->getUser() !== $this->getUser()) {
            return $this->render(self::$TEMPLATE_DIR . 'error/wrongUser.html.twig');
        }

        if ($participation->getState() !== ParticipationStateTypes::$ONGOING) {
            return $this->render(self::$TEMPLATE_DIR . 'error/invalidState.html.twig');
        }

        return null;
    }

    /** @return QuestionnaireQuestion[] */
    private function getSortedQuestions(Questionnaire $questionnaire): array
    {
        return $this->questionnaireQuestionRepository->createQueryBuilder('qq')
            ->where('qq.questionnaire = :questionnaire')
            ->setParameter('questionnaire', $questionnaire)
            ->orderBy('qq.step', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @param QuestionnaireQuestion[] $sortedQuestions */
    private function getPreviousQuestionnaireQuestion(
        array               $sortedQuestions,
        ParticipationAnswer $latestParticipationAnswer,
    ): ?QuestionnaireQuestion
    {
        foreach ($sortedQuestions as $questionnaireQuestion) {
            if ($questionnaireQuestion->getQuestion() === $latestParticipationAnswer->getQuestion()) {
                return $questionnaireQuestion;
            }
        }

        return null;
    }

    private function getNextQuestionnaireQuestionByQuestionnaireQuestion(
        QuestionnaireQuestion $questionnaireQuestion,
    ): QuestionnaireQuestion
    {
        return $this->questionnaireQuestionRepository->createQueryBuilder('qq')
            ->where('qq.questionnaire = :questionnaire')
            ->setParameter('questionnaire', $questionnaireQuestion->getQuestionnaire())
            ->andWhere('qq.step > :step')
            ->setParameter('step', $questionnaireQuestion->getStep())
            ->orderBy('qq.step', 'ASC')
            ->getQuery()
            ->getResult()[0];
    }

    private
    function handleForm(FormInterface $form, Participation $participation): void
    {
        $questions = $form->getData();
        $questionnaire = $participation->getQuestionnaire();

        foreach ($questions as $questionId => $answers) {
            if ($answers === null) {
                return;
            }

            if (is_array($answers)) {
                foreach ($answers as $answer) {
                    $participationAnswer = (new ParticipationAnswer())
                        ->setParticipation($participation)
                        ->setAnswer($answer)
                        ->setQuestion($this->questionRepository->find($questionId))
                        ->setQuestionnaire($questionnaire);

                    $this->em->persist($participationAnswer);
                }
            } else {
                $participationAnswer = (new ParticipationAnswer())
                    ->setParticipation($participation)
                    ->setAnswer($answers)
                    ->setQuestion($this->questionRepository->find($questionId))
                    ->setQuestionnaire($questionnaire);

                $this->em->persist($participationAnswer);
            }
        }

        $this->em->flush();
    }
}