<?php

namespace App\Entity;

use App\Repository\SettingsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=SettingsRepository::class)
 */
class Settings
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $useKeycloakGroups;

    public function getUseKeycloakGroups(): ?bool
    {
        return $this->useKeycloakGroups;
    }

    public function setUseKeycloakGroups(?bool $useKeycloakGroups): self
    {
        $this->useKeycloakGroups = $useKeycloakGroups;

        return $this;
    }
}
