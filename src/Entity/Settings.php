<?php

namespace App\Entity;

use App\Repository\SettingsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SettingsRepository::class)]
class Settings
{
    const NO_GROUP_MAPPING = 0;

    const KEYCLOAK_GROUP_MAPPING = 1;

    const API_GROUP_MAPPING = 2;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(options: [
        'default' => 0,
    ])]
    #[Assert\Choice([
        self::NO_GROUP_MAPPING,
        self::KEYCLOAK_GROUP_MAPPING,
        self::API_GROUP_MAPPING,
    ])]
    private int $groupMapping = 0;

    public function getGroupMapping(): int
    {
        return $this->groupMapping;
    }

    public function setGroupMapping(int $groupMapping): static
    {
        $this->groupMapping = $groupMapping;

        return $this;
    }

    public function getUseKeycloakGroups(): bool
    {
        return $this->groupMapping === self::KEYCLOAK_GROUP_MAPPING;
    }
}
