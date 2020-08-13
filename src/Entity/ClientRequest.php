<?php

namespace App\Entity;

use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;
use App\Repository\ClientRequestRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ClientRequestRepository::class)
 */
class ClientRequest
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Encrypted()
     */
    private $uuid;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Encrypted()
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Encrypted()
     */
    private $description;

    /**
     * @ORM\Column(type="integer")
     */
    private $item;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Encrypted()
     */
    private $email;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Encrypted()
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="clientRequests")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="assignedRequests")
     */
    private $assignedUser;

    /**
     * @ORM\Column(type="boolean")
     */
    private $emailValid;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $token;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    private $activ;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="clientRequests")
     * @ORM\JoinColumn(nullable=false)
     */
    private $team;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getItem(): ?int
    {
        return $this->item;
    }

    public function setItem(int $item): self
    {
        $this->item = $item;

        return $this;
    }

    public function getItemString()
    {
        switch ($this->item) {
            case 0:
                return 'Antrag auf Auskunft';
                break;
            case 10:
                return 'Antrag auf Berichtigung';
                break;
            case 20:
                return 'Antrag auf Datenübertragung';
                break;
            case 30:
                return 'Antrag auf Löschung';
                break;
            default:
                return "Nicht ausgewählt";
                break;
        }
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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

    public function getEmailValid(): ?bool
    {
        return $this->emailValid;
    }

    public function setEmailValid(bool $emailValid): self
    {
        $this->emailValid = $emailValid;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

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
}
