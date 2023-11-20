<?php
declare(strict_types=1);

namespace App\Controller;

use App\DataTypes\QuestionTypes;
use App\Entity\Question;
use App\Entity\Questionnaire;
use App\Entity\Team;
use App\Entity\User;
use App\Form\Type\Questionnaire\QuestionType;
use App\Repository\AnswerRepository;
use App\Repository\ParticipationAnswerRepository;
use App\Repository\QuestionnaireQuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/questionnaire/question', name: 'question')]
class QuestionController extends AbstractController
{
    private static string $TEMPLATE_DIR = 'questionnaire/question/';

    public function __construct(
        private EntityManagerInterface $em,
    )
    {
    }

    #[Route(path: '/create/{id}', name: '_create', methods: ['POST', 'GET'])]
    #[ParamConverter('questionnaire', class: 'App\Entity\Questionnaire', options: ['mapping' => ['id' => 'id']])]
    public function create(Request $request, Questionnaire $questionnaire): Response
    {
        $form = $this->createForm(QuestionType::class);
        $error = false;
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $question = $this->handleForm($form);

            if ($question !== null) {
                return $this->redirectToRoute(
                    'questionnaire_add_question',
                    [
                        'questionnaireId' => $questionnaire->getId(),
                        'questionId' => $question->getId()
                    ]
                );
            }

            $error = true;
        }

        return $this->render(
            self::$TEMPLATE_DIR . 'create.html.twig',
            [
                'form' => $form->createView(),
                'error' => $error,
            ]
        );
    }

    #[Route(path: '/edit/{id}', name: '_edit', methods: ['POST', 'GET'])]
    #[ParamConverter('question', class: 'App\Entity\Question', options: ['mapping' => ['id' => 'id']])]
    public function edit(Request $request, Question $question): Response
    {
        $form = $this->createForm(QuestionType::class, $question);
        $error = false;

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $question = $this->handleForm($form, $question);

            if ($question !== null) {
                return $this->redirectToRoute('question_details', ['id' => $question->getId()]);
            }

            $error = true;
        }

        return $this->render(
            self::$TEMPLATE_DIR . 'edit.html.twig',
            [
                'form' => $form->createView(),
                'error' => $error,
            ]
        );
    }

    #[Route(path: '/details/{id}', name: '_details', methods: ['POST', 'GET'])]
    #[ParamConverter('question', class: 'App\Entity\Question', options: ['mapping' => ['id' => 'id']])]
    public function details(Question $question): Response
    {
        return $this->render(
            self::$TEMPLATE_DIR . 'details.html.twig',
            [
                'question' => $question
            ],
        );
    }

    #[Route(path: '/delete/{id}', name: '_delete', methods: ['POST', 'GET'])]
    #[ParamConverter('question', class: 'App\Entity\Question', options: ['mapping' => ['id' => 'id']])]
    public function delete(
        Question                        $question,
        QuestionnaireQuestionRepository $questionnaireQuestionRepository,
        AnswerRepository                $answerRepository,
        ParticipationAnswerRepository   $participationAnswerRepository,
    ): RedirectResponse
    {
        foreach ($participationAnswerRepository->findBy(['question' => $question]) as $participationAnswer) {
            $this->em->remove($participationAnswer);
        }

        foreach ($questionnaireQuestionRepository->findBy(['question' => $question]) as $questionnaireQuestion) {
            $this->em->remove($questionnaireQuestion);
        };

        foreach ($answerRepository->findBy(['question' => $question]) as $answer) {
            $this->em->remove($answer);
        }


        $this->em->remove($question);
        $this->em->flush();

        return $this->redirectToRoute('akademie_admin');
    }

    private function handleForm(FormInterface $form, ?Question $originalQuestion = null): ?Question
    {
        if (!$form->isValid()) {
            return null;
        }
        /** @var Question $question */
        $question = $form->getData();

        if ($originalQuestion !== null) {
            foreach ($originalQuestion->getAnswers() as $originalAnswer) {
                if (!in_array($originalAnswer, $question->getAnswers()->toArray())) {
                    $this->em->remove($originalAnswer);
                }
            }
        }

        $trueAnswers = [];
        foreach ($question->getAnswers() as $answer) {
            if ($answer->isCorrect()) {
                $trueAnswers[] = $answer;
            }
        }

        if (count($trueAnswers) === 0) {
            return null;
        }

        if ($question->getType() === QuestionTypes::RADIO && count($trueAnswers) !== 1) {
            return null;
        }

        $this->em->persist($question);
        $this->em->flush();

        return $question;
    }
}