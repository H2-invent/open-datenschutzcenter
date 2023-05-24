<?php
declare(strict_types=1);

namespace App\Entity;

use App\Entity\Templates\EntityWithTimestamps;
use App\Repository\QuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Question extends EntityWithTimestamps
{
    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $label;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $hint = null;

    #[ORM\Column(type: Types::FLOAT)]
    private float $evalValue = 1.0;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $type;

    #[ORM\OneToMany(mappedBy: 'question', targetEntity: Answer::class, cascade: ['persist'])]
    private Collection $answers;

    #[ORM\OneToMany(mappedBy: 'question', targetEntity: QuestionnaireQuestion::class)]
    private Collection $questionnaireQuestions;

    #[ORM\OneToMany(mappedBy: 'question', targetEntity: ParticipationAnswer::class)]
    private Collection $participationAnswers;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
        $this->questionnaireQuestions = new ArrayCollection();
        $this->participationAnswers = new ArrayCollection();
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    public function getHint(): ?string
    {
        return $this->hint;
    }

    public function setHint(string $hint): self
    {
        $this->hint = $hint;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getEvalValue(): float
    {
        return $this->evalValue;
    }

    public function setEvalValue(float $evalValue): self
    {
        $this->evalValue = $evalValue;
        return $this;
    }

    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(Answer $answer): self
    {
        if(!$this->answers->contains($answer)){
            $this->answers->add($answer);
            $answer->setQuestion($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): self
    {
        if($this->answers->removeElement($answer)){
            if($answer->getQuestion() === $this){
                $answer->setQuestion(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, QuestionnaireQuestion>
     */
    public function getQuestionnaireQuestions(): Collection
    {
        return $this->questionnaireQuestions;
    }

    public function addQuestionnaireQuestion(QuestionnaireQuestion $questionnaireQuestion): self
    {
        if (!$this->questionnaireQuestions->contains($questionnaireQuestion)) {
            $this->questionnaireQuestions->add($questionnaireQuestion);
            $questionnaireQuestion->setQuestion($this);
        }

        return $this;
    }

    public function removeQuestionnaireQuestion(QuestionnaireQuestion $questionnaireQuestion): self
    {
        if ($this->questionnaireQuestions->removeElement($questionnaireQuestion)) {
            // set the owning side to null (unless already changed)
            if ($questionnaireQuestion->getQuestion() === $this) {
                $questionnaireQuestion->setQuestion(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ParticipationAnswer>
     */
    public function getParticipationAnswers(): Collection
    {
        return $this->participationAnswers;
    }

    public function addParticipationAnswer(ParticipationAnswer $participationAnswer): self
    {
        if (!$this->participationAnswers->contains($participationAnswer)) {
            $this->participationAnswers->add($participationAnswer);
            $participationAnswer->setQuestion($this);
        }

        return $this;
    }

    public function removeParticipationAnswer(ParticipationAnswer $participationAnswer): self
    {
        if ($this->participationAnswers->removeElement($participationAnswer)) {
            // set the owning side to null (unless already changed)
            if ($participationAnswer->getQuestion() === $this) {
                $participationAnswer->setQuestion(null);
            }
        }

        return $this;
    }
}