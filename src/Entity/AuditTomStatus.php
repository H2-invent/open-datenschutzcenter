<?php

namespace App\Entity;

use App\Repository\AuditTomStatusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AuditTomStatusRepository::class)]
class AuditTomStatus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'text')]
    private $name;

    #[ORM\Column(type: 'text')]
    private $color;

    #[ORM\OneToMany(targetEntity: AuditTom::class, mappedBy: 'status')]
    private $auditToms;

    public function __construct()
    {
        $this->auditToms = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @return Collection|AuditTom[]
     */
    public function getAuditToms(): Collection
    {
        return $this->auditToms;
    }

    public function addAuditTom(AuditTom $auditTom): self
    {
        if (!$this->auditToms->contains($auditTom)) {
            $this->auditToms[] = $auditTom;
            $auditTom->setStatus($this);
        }

        return $this;
    }

    public function removeAuditTom(AuditTom $auditTom): self
    {
        if ($this->auditToms->contains($auditTom)) {
            $this->auditToms->removeElement($auditTom);
            // set the owning side to null (unless already changed)
            if ($auditTom->getStatus() === $this) {
                $auditTom->setStatus(null);
            }
        }

        return $this;
    }
}
