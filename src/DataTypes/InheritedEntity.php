<?php

namespace App\DataTypes;

use App\Entity\Loeschkonzept;
use App\Entity\VVTDatenkategorie;

class InheritedEntity
{
    private ?VVTDatenkategorie $category;

    private ?Loeschkonzept $deletionConcept;

    public function __construct()
    {
        $this->category = null;
        $this->deletionConcept = null;
    }

    public function getCategory(): ?VVTDatenkategorie
    {
        return $this->category;
    }

    public function setCategory(?VVTDatenkategorie $category): InheritedEntity
    {
        $this->category = $category;
        return $this;
    }

    public function getDeletionConcept(): ?Loeschkonzept
    {
        return $this->deletionConcept;
    }

    public function setDeletionConcept(?Loeschkonzept $deletionConcept): InheritedEntity
    {
        $this->deletionConcept = $deletionConcept;
        return $this;
    }
}
