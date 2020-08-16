<?php

namespace App\Entity;

use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;
use App\Repository\ClientRequestRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * @ORM\Column(type="text", unique=true)
     * @Assert\NotBlank()
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
     * @Assert\Email()
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
     * @ORM\Column(type="text", unique=true)
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

    /**
     * @ORM\OneToMany(targetEntity=ClientComment::class, mappedBy="clientRequest")
     */
    private $clientComments;

    /**
     * @ORM\Column(type="boolean")
     */
    private $validUser;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     */
    private $userValidBy;

    /**
     * @ORM\Column(type="boolean")
     */
    private $gdpr;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Encrypted()
     */
    private $notes;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Encrypted()
     */
    private $pgp;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $password;

    /**
     * @ORM\Column(type="boolean")
     */
    private $open;

    public function __construct()
    {
        $this->clientComments = new ArrayCollection();
    }

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
                return 'Antrag auf Auskunft nach Art. 15 DSGVO';
                break;
            case 10:
                return 'Antrag auf Berichtigung nach Art. 16 DSGVO';
                break;
            case 15:
                return 'Antrag auf Einschränkung nach Art. 18 DSGVO';
                break;
            case 20:
                return 'Antrag auf Datenübertragung/Herausgabe nach Art. 20 DSGVO';
                break;
            case 30:
                return 'Antrag auf Löschung (Recht auf Vergessenwerden) nach Art. 17 DSGVO';
                break;
            case 40:
                return 'Antrag auf Widerruf einer Einwilligung nach Art. 7 Abs. 3 DSGVO';
                break;
            case 50:
                return 'Antrag auf einzelfallbezogenes Widerspruchsrecht nach Art. 21 DSGVO';
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

    /**
     * @return Collection|ClientComment[]
     */
    public function getClientComments(): Collection
    {
        return $this->clientComments;
    }

    public function addClientComment(ClientComment $clientComment): self
    {
        if (!$this->clientComments->contains($clientComment)) {
            $this->clientComments[] = $clientComment;
            $clientComment->setClientRequest($this);
        }

        return $this;
    }

    public function removeClientComment(ClientComment $clientComment): self
    {
        if ($this->clientComments->contains($clientComment)) {
            $this->clientComments->removeElement($clientComment);
            // set the owning side to null (unless already changed)
            if ($clientComment->getClientRequest() === $this) {
                $clientComment->setClientRequest(null);
            }
        }

        return $this;
    }

    public function getValidUser(): ?bool
    {
        return $this->validUser;
    }

    public function setValidUser(bool $validUser): self
    {
        $this->validUser = $validUser;

        return $this;
    }

    public function getUserValidBy(): ?User
    {
        return $this->userValidBy;
    }

    public function setUserValidBy(?User $userValidBy): self
    {
        $this->userValidBy = $userValidBy;

        return $this;
    }

    public function getGdpr(): ?bool
    {
        return $this->gdpr;
    }

    public function setGdpr(bool $gdpr): self
    {
        $this->gdpr = $gdpr;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(string $notes): ?self
    {
        $this->notes = $notes;

        return $this;
    }

    public function getPgp(): ?string
    {
        return $this->pgp;
    }

    public function setPgp(?string $pgp): self
    {
        $this->pgp = $pgp;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getOpen(): ?bool
    {
        return $this->open;
    }

    public function setOpen(bool $open): self
    {
        $this->open = $open;

        return $this;
    }
}
