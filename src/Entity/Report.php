<?php

namespace App\Entity;

use App\Repository\ReportRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReportRepository::class)]
class Report
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\Column(type: 'date')]
    private $date;

    #[ORM\Column(type: 'time')]
    private $start;

    #[ORM\Column(type: 'time')]
    private $end;

    #[ORM\Column(type: 'float')]
    private $calcTime;

    #[ORM\Column(type: 'text')]
    private $description;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $invoice;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $onsite;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $inReport;

    #[ORM\Column(type: 'integer')]
    private $activ;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'reports')]
    #[ORM\JoinColumn(nullable: false)]
    private $team;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(\DateTimeInterface $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(\DateTimeInterface $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function getCalcTime(): ?float
    {
        return $this->calcTime;
    }

    public function setCalcTime(float $calcTime): self
    {
        $this->calcTime = $calcTime;

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

    public function getInvoice(): ?bool
    {
        return $this->invoice;
    }

    public function setInvoice(?bool $invoice): self
    {
        $this->invoice = $invoice;

        return $this;
    }

    public function getOnsite(): ?bool
    {
        return $this->onsite;
    }

    public function setOnsite(?bool $onsite): self
    {
        $this->onsite = $onsite;

        return $this;
    }

    public function getInReport(): ?bool
    {
        return $this->inReport;
    }

    public function setInReport(?bool $inReport): self
    {
        $this->inReport = $inReport;

        return $this;
    }

    public function getActiv(): ?int
    {
        return $this->activ;
    }

    public function setActiv(int $activ): self
    {
        $this->activ = $activ;

        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
