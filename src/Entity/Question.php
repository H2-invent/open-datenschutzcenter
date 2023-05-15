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
class Question extends EntityWithTimestamps
{
    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $label;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $hintLabel = null;

    #[ORM\Column(type: Types::FLOAT)]
    private float $evalValue = 1.0;

    #[ORM\OneToMany(mappedBy: 'question', targetEntity: Answer::class)]
    private Collection $answers;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
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

    public function getHintLabel(): string
    {
        return $this->hintLabel;
    }

    public function setHintLabel(string $hintLabel): self
    {
        $this->hintLabel = $hintLabel;
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
}