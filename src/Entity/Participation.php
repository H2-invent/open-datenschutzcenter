<?php

namespace App\Entity;

use App\Entity\Templates\EntityWithTimestamps;
use App\Repository\ParticipationRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParticipationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Participation extends EntityWithTimestamps
{
    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $completedAt = null;

    #[ORM\Column(length: 255)]
    private ?string $state = null;

    #[ORM\Column]
    private ?bool $passed = null;

    #[ORM\ManyToOne(inversedBy: 'participations')]
    #[ORM\JoinColumn(name: 'akademie_buchungen_id')]
    private AkademieBuchungen $academyBilling;

    #[ORM\OneToMany(mappedBy: 'participation', targetEntity: ParticipationAnswer::class)]
    private Collection $participationAnswers;

    #[ORM\ManyToOne(targetEntity: Questionnaire::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Questionnaire $questionnaire;

    public function __construct()
    {
        $this->participationAnswers = new ArrayCollection();
    }

    public function getCompletedAt(): ?DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?DateTimeImmutable $completedAt): self
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function isPassed(): ?bool
    {
        return $this->passed;
    }

    public function setPassed(bool $passed): self
    {
        $this->passed = $passed;

        return $this;
    }

    public function getAcademyBilling(): ?AkademieBuchungen
    {
        return $this->academyBilling;
    }

    public function setAcademyBilling(?AkademieBuchungen $academyBilling): self
    {
        $this->academyBilling = $academyBilling;

        return $this;
    }

    public function getQuestionnaire(): Questionnaire
    {
        return $this->questionnaire;
    }

    public function setQuestionnaire(Questionnaire $questionnaire): self
    {
        $this->questionnaire = $questionnaire;

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
            $participationAnswer->setParticipation($this);
        }

        return $this;
    }

    public function removeParticipationAnswer(ParticipationAnswer $participationAnswer): self
    {
        if ($this->participationAnswers->removeElement($participationAnswer)) {
            // set the owning side to null (unless already changed)
            if ($participationAnswer->getParticipation() === $this) {
                $participationAnswer->setParticipation(null);
            }
        }

        return $this;
    }
}
