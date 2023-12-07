<?php
declare(strict_types=1);

namespace App\Controller;

use App\DataTypes\ParticipationStateTypes;
use App\Entity\AkademieBuchungen;
use App\Entity\Answer;
use App\Entity\Participation;
use App\Entity\Question;
use App\Entity\Questionnaire;
use App\Entity\Team;
use App\Entity\User;
use App\Form\Type\Questionnaire\Question\DynamicQuestionType;
use App\Service\CurrentTeamService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route(path: '/participation', name: 'participation')]
class ParticipationController extends BaseController
{
    private static string $TEMPLATE_DIR = 'questionnaire/participation/';

    public function __construct(
        private CurrentTeamService $currentTeamService,
        private EntityManagerInterface $em,
    ){}

    #[Route(path: '/{id}/start', name: '_start', methods:['GET', 'POST'])]
    #[ParamConverter(data: 'participation', class: Participation::class, options: ['mapping' => ['id' => 'id']])]
    public function start(Participation $participation): RedirectResponse{
        $participation->setState(ParticipationStateTypes::$ONGOING);

        $this->em->persist($participation);
        $this->em->flush();

        return $this->redirectToRoute('participation_question', ['id' => $participation->getId()]);
    }

    #[Route(path: '/reset/{id}', name: '_reset')]
    #[ParamConverter(data: 'participation', class: Participation::class, options: ['mapping' => ['id' => 'id']])]
    public function reset(Participation $oldParticipation): RedirectResponse
    {
        if($oldParticipation->isPassed()){
            return $this->redirectToRoute('akademie');
        }

        $billing = $oldParticipation->getAcademyBilling();
        $questionnaire = $oldParticipation->getQuestionnaire();

        $this->em->remove($oldParticipation);

        $participation = (new Participation())
            ->setState(ParticipationStateTypes::$ASSIGNED)
            ->setAcademyBilling($billing)
            ->setQuestionnaire($questionnaire);

        $this->em->persist($participation);
        $this->em->flush();

        return $this->redirectToRoute('akademie');
    }

    #[Route(path: '/{id}/continue', name: '_continue', methods:['GET', 'POST'])]
    #[ParamConverter(data: 'participation', class: Participation::class, options: ['mapping' => ['id' => 'id']])]
    public function continue(Participation $participation): RedirectResponse
    {
        return $this->redirectToRoute('participation_question', ['id' => $participation->getId()]);
    }

    #[Route(path: '/{id}/finish', name: '_finish', methods: ['GET', 'POST'])]
    #[ParamConverter(data: 'participation', class: Participation::class, options: ['mapping' => ['id' => 'id']])]
    public function finish(Participation $participation): Response
    {
        $this->evaluate($participation);
        $this->removeParticipationAnswers($participation);

        return $this->render('questionnaire/result.html.twig', [
            'participation' => $participation
        ]);
    }

    private function evaluate(Participation $participation): Participation
    {
        $questionnaire = $participation->getQuestionnaire();

        $questions = [];
        $overallPoints = 0;
        foreach($questionnaire->getQuestionnaireQuestions() as $questionnaireQuestion) {
            $question = $questionnaireQuestion->getQuestion();
            $questions[] = $question;
            $overallPoints += $question->getEvalValue();
        }

        $achievedPoints = 0;

        foreach($questions as $question) {
            if($this->evaluateQuestion($question, $participation)){
                $achievedPoints += $question->getEvalValue();
            }
        }

        $participation->setPassed($questionnaire->getPercentageToPass() <= ($achievedPoints/$overallPoints));
        $participation->setState(ParticipationStateTypes::$FINISHED);

        return $participation;
    }

    private function evaluateQuestion(Question $question, Participation $participation): bool {
        $givenAnswers = [];

        foreach($participation->getParticipationAnswers() as $participationAnswer){
            if($participationAnswer->getQuestion() === $question){
                $givenAnswer = $participationAnswer->getAnswer();

                if(!$givenAnswer->isCorrect()){
                    return false;
                }

                $givenAnswers[] = $givenAnswer;
            }
        }

        /** @var Answer $answer */
        foreach ($question->getAnswers() as $answer){
            if($answer->isCorrect() && !in_array($answer, $givenAnswers)){
                return false;
            }
        }

        return true;
    }

    private function removeParticipationAnswers(Participation $participation): void
    {
        foreach($participation->getParticipationAnswers() as $participationAnswer) {
            $this->em->remove($participationAnswer);
        }

        $this->em->flush();
    }
}