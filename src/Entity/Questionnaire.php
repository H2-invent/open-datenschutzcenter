<?php

namespace App\Entity;

use App\Entity\Templates\EntityWithTimestamps;
use App\Repository\QuestionnaireRepository;
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
}
