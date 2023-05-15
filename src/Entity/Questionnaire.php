<?php

namespace App\Entity;

use App\Entity\Templates\EntityWithTimestamps;
use App\Repository\QuestionnaireRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestionnaireRepository::class)]
class Questionnaire extends EntityWithTimestamps
{
    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $label;

    #[ORM\Column(type: Types::TEXT, length: 65535, nullable: true)]
    private ?string $descriptionLabel = null;

    #[ORM\Column(type: Types::FLOAT)]
    private float $percentageToPass;

    #[ORM\OneToMany(mappedBy: 'Questionnaire', targetEntity: QuestionnaireQuestion::class)]
    private Collection $questionnaireQuestions;

    #[ORM\OneToMany(mappedBy: 'questionnaire', targetEntity: ParticipationAnswer::class)]
    private Collection $participationAnswers;

    public function __construct()
    {
        $this->questionnaireQuestions = new ArrayCollection();
        $this->participationAnswers = new ArrayCollection();
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getDescriptionLabel(): ?string
    {
        return $this->descriptionLabel;
    }

    public function setDescriptionLabel(string $descriptionLabel): self
    {
        $this->descriptionLabel = $descriptionLabel;

        return $this;
    }

    public function getPercentageToPass(): ?float
    {
        return $this->percentageToPass;
    }

    public function setPercentageToPass(float $percentageToPass): self
    {
        $this->percentageToPass = $percentageToPass;

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
            $questionnaireQuestion->setQuestionnaire($this);
        }

        return $this;
    }

    public function removeQuestionnaireQuestion(QuestionnaireQuestion $questionnaireQuestion): self
    {
        if ($this->questionnaireQuestions->removeElement($questionnaireQuestion)) {
            // set the owning side to null (unless already changed)
            if ($questionnaireQuestion->getQuestionnaire() === $this) {
                $questionnaireQuestion->setQuestionnaire(null);
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
            $participationAnswer->setQuestionnaire($this);
        }

        return $this;
    }

    public function removeParticipationAnswer(ParticipationAnswer $participationAnswer): self
    {
        if ($this->participationAnswers->removeElement($participationAnswer)) {
            // set the owning side to null (unless already changed)
            if ($participationAnswer->getQuestionnaire() === $this) {
                $participationAnswer->setQuestionnaire(null);
            }
        }

        return $this;
    }
}
