<?php

namespace App\Entity;

use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;
use App\Repository\TaskRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $activ;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'tasks')]
    private $assignedUser;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    /**
     * @Encrypted()
     */
    #[ORM\Column(type: 'text')]
    private $task;

    #[ORM\Column(type: 'boolean')]
    private $done;

    /**
     * @Encrypted()
     */
    #[ORM\Column(type: 'text')]
    private $title;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'tasks')]
    private $team;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private $user;

    #[ORM\Column(type: 'date', nullable: true)]
    private $endDate;

    #[ORM\Column(type: 'integer')]
    private $prio;

    #[ORM\Column(type: 'date', nullable: true)]
    private $doneDate;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private $updatedBy;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAssignedUser(): ?User
    {
        return $this->assignedUser;
    }

    public function setAssignedUser(?User $assignedUser): self
    {
        $this->assignedUser = $assignedUser;

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

    public function getTask(): ?string
    {
        return $this->task;
    }

    public function setTask(string $task): self
    {
        $this->task = $task;

        return $this;
    }

    public function getDone(): ?bool
    {
        return $this->done;
    }

    public function setDone(bool $done): self
    {
        $this->done = $done;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getPrio(): ?int
    {
        return $this->prio;
    }

    public function getPrioString()
    {
        switch ($this->prio) {
            case 0:
                return 'Ohne Priorität';
                break;
            case 1:
                return 'Wenig Wichtig';
                break;
            case 2:
                return 'Normal';
                break;
            case 3:
                return 'Wichtig';
                break;
            case 4:
                return 'Sehr wichtig';
                break;
            default:
                return "Ohne Priorität";
                break;
        }
    }

    public function setPrio(int $prio): self
    {
        $this->prio = $prio;

        return $this;
    }

    public function getDoneDate(): ?\DateTimeInterface
    {
        return $this->doneDate;
    }

    public function setDoneDate(?\DateTimeInterface $doneDate): self
    {
        $this->doneDate = $doneDate;

        return $this;
    }

    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?User $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }
}
