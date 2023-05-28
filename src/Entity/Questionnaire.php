<?php

namespace App\Entity;

use App\Entity\Templates\EntityWithTimestamps;
use App\Repository\QuestionnaireRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestionnaireRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Questionnaire extends EntityWithTimestamps
{
    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $label;

    #[ORM\Column(type: Types::TEXT, length: 65535, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::FLOAT)]
    private float $percentageToPass;

    #[ORM\OneToMany(mappedBy: 'questionnaire', targetEntity: QuestionnaireQuestion::class)]
    private Collection $questionnaireQuestions;

    #[ORM\OneToMany(mappedBy: 'questionnaire', targetEntity: ParticipationAnswer::class)]
    private Collection $participationAnswers;

    #[ORM\OneToMany(mappedBy: 'questionnaire', targetEntity: AkademieKurse::class)]
    private Collection $academyLessons;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'questionnaires')]
    #[ORM\JoinColumn(nullable: false)]
    private Team $team;

    public function __construct()
    {
        $this->questionnaireQuestions = new ArrayCollection();
        $this->participationAnswers = new ArrayCollection();
        $this->academyLessons = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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

    public function getAcademyLessons(): Collection
    {
        return $this->academyLessons;
    }

    public function addAcademyLesson(AkademieKurse $academyLesson): self
    {
        if (!$this->academyLessons->contains($academyLesson)) {
            $this->academyLessons->add($academyLesson);
            $academyLesson->setQuestionnaire($this);
        }

        return $this;
    }

    public function removeAcademyLesson(AkademieKurse $academyLesson): self
    {
        if ($this->academyLessons->removeElement($academyLesson)) {
            // set the owning side to null (unless already changed)
            if ($academyLesson->getQuestionnaire() === $this) {
                $academyLesson->setQuestionnaire(null);
            }
        }

        return $this;
    }

    public function setTeam(Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    public function getTeam(): Team
    {
        return $this->team;
    }
}
