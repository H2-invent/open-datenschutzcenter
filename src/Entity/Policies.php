<?php

namespace App\Entity;

use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;
use App\Repository\PoliciesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=PoliciesRepository::class)
 */
class Policies
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="policies")
     * @ORM\JoinColumn(nullable=false)
     */
    private $team;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="policies")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $activ;

    /**
     * @ORM\OneToOne(targetEntity=Policies::class, cascade={"persist", "remove"})
     */
    private $previous;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Encrypted()
     */
    private $scope;

    /**
     * @ORM\ManyToMany(targetEntity=VVT::class, inversedBy="policies")
     * @Assert\NotBlank()
     */
    private $processes;

    /**
     * @ORM\ManyToMany(targetEntity=VVTPersonen::class)
     * @Assert\NotBlank()
     */
    private $people;

    /**
     * @ORM\ManyToMany(targetEntity=VVTDatenkategorie::class)
     * @Assert\NotBlank()
     */
    private $categories;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Encrypted()
     */
    private $risk;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $foundation;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="policiesResponsible")
     * @ORM\JoinColumn(nullable=false)
     */
    private $person;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $protection;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Encrypted()
     */
    private $notes;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Encrypted()
     */
    private $consequences;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Encrypted()
     */
    private $contact;

    /**
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="assignedPolicies")
     */
    private $assignedUser;

    public function __construct()
    {
        $this->processes = new ArrayCollection();
        $this->people = new ArrayCollection();
        $this->categories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getActiv(): ?bool
    {
        return $this->activ;
    }

    public function setActiv(bool $activ): self
    {
        $this->activ = $activ;

        return $this;
    }

    public function getPrevious(): ?self
    {
        return $this->previous;
    }

    public function setPrevious(?self $previous): self
    {
        $this->previous = $previous;

        return $this;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function setScope(string $scope): self
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * @return Collection|VVT[]
     */
    public function getProcesses(): Collection
    {
        return $this->processes;
    }

    public function addProcess(VVT $process): self
    {
        if (!$this->processes->contains($process)) {
            $this->processes[] = $process;
        }

        return $this;
    }

    public function removeProcess(VVT $process): self
    {
        if ($this->processes->contains($process)) {
            $this->processes->removeElement($process);
        }

        return $this;
    }

    /**
     * @return Collection|VVTPersonen[]
     */
    public function getPeople(): Collection
    {
        return $this->people;
    }

    public function addPerson(VVTPersonen $person): self
    {
        if (!$this->people->contains($person)) {
            $this->people[] = $person;
        }

        return $this;
    }

    public function removePerson(VVTPersonen $person): self
    {
        if ($this->people->contains($person)) {
            $this->people->removeElement($person);
        }

        return $this;
    }

    /**
     * @return Collection|VVTDatenkategorie[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(VVTDatenkategorie $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
        }

        return $this;
    }

    public function removeCategory(VVTDatenkategorie $category): self
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
        }

        return $this;
    }

    public function getRisk(): ?string
    {
        return $this->risk;
    }

    public function setRisk(string $risk): self
    {
        $this->risk = $risk;

        return $this;
    }

    public function getFoundation(): ?string
    {
        return $this->foundation;
    }

    public function setFoundation(string $foundation): self
    {
        $this->foundation = $foundation;

        return $this;
    }

    public function getPerson(): ?User
    {
        return $this->person;
    }

    public function setPerson(?User $person): self
    {
        $this->person = $person;

        return $this;
    }

    public function getProtection(): ?string
    {
        return $this->protection;
    }

    public function setProtection(string $protection): self
    {
        $this->protection = $protection;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    public function getConsequences(): ?string
    {
        return $this->consequences;
    }

    public function setConsequences(?string $consequences): self
    {
        $this->consequences = $consequences;

        return $this;
    }

    public function getContact(): ?string
    {
        return $this->contact;
    }

    public function setContact(?string $contact): self
    {
        $this->contact = $contact;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStatusString()
    {
        switch ($this->status) {
            case 0:
                return 'Angelegt';
                break;
            case 1:
                return 'In Bearbeitung';
                break;
            case 2:
                return 'Prüfung';
                break;
            case 3:
                return 'Freigegeben';
                break;
            case 4:
                return 'Veraltet';
                break;
            default:
                return "Angelegt";
                break;
        }
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
}
