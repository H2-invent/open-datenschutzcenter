<?php
declare(strict_types=1);

namespace App\Entity\Templates;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

abstract class EntityWithTimestamps
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected int $id;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: false)]
    protected DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: false)]
    protected DateTimeImmutable $updatedAt;

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    #[ORM\PrePersist]
    public function setCreatedAt(): self
    {
        if(!isset($this->createdAt)){
            $this->createdAt = new DateTimeImmutable();
        }

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist]
    public function setUpdatedAt(): self
    {
        if(!isset($this->updatedAt)){
            $this->updatedAt = new DateTimeImmutable();
        }

        return $this;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}