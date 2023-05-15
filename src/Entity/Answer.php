<?php
declare(strict_types=1);

namespace App\Entity;

use App\Entity\Templates\EntityWithTimestamps;
use App\Repository\AnswerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnswerRepository::class)]
class Answer extends EntityWithTimestamps
{
    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $label;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isCorrect;

    #[ORM\ManyToOne(inversedBy: 'question')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Question $question;

    #[ORM\OneToMany(mappedBy: 'answer', targetEntity: ParticipationAnswer::class)]
    private Collection $participationAnswers;

    public function __construct()
    {
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

    public function isCorrect(): bool
    {
        return $this->isCorrect;
    }

    public function setIsCorrect(bool $isCorrect): self
    {
        $this->isCorrect = $isCorrect;

        return $this;
    }

    public function getQuestion(): Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): self
    {
        $this->question = $question;

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
            $participationAnswer->setAnswer($this);
        }

        return $this;
    }

    public function removeParticipationAnswer(ParticipationAnswer $participationAnswer): self
    {
        if ($this->participationAnswers->removeElement($participationAnswer)) {
            // set the owning side to null (unless already changed)
            if ($participationAnswer->getAnswer() === $this) {
                $participationAnswer->setAnswer(null);
            }
        }

        return $this;
    }
}