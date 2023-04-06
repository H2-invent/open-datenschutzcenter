<?php

namespace App\Entity;

use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;
use App\Repository\AuditTomRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AuditTomRepository::class)]
class AuditTom
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    /**
     * @Assert\NotBlank()
     * @Encrypted()
     */
    #[ORM\Column(type: 'text')]
    private $frage;

    /**
     * @Encrypted()
     * @Assert\NotBlank()
     */
    #[ORM\Column(type: 'text')]
    private $bemerkung;

    /**
     * @Encrypted()
     * @Assert\NotBlank()
     */
    #[ORM\Column(type: 'text')]
    private $empfehlung;

    /**
     * @Assert\NotBlank()
     */
    #[ORM\ManyToMany(targetEntity: AuditTomZiele::class)]
    private $ziele;

    /**
     * @Assert\NotBlank()
     */
    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'auditToms')]
    #[ORM\JoinColumn(nullable: false)]
    private $team;

    /**
     * @Assert\NotBlank()
     */
    #[ORM\ManyToMany(targetEntity: AuditTomAbteilung::class)]
    private $abteilung;

    /**
     * @Assert\NotBlank()
     */
    #[ORM\ManyToOne(targetEntity: AuditTomStatus::class, inversedBy: 'auditToms')]
    #[ORM\JoinColumn(nullable: true)]
    private $status;

    #[ORM\Column(type: 'boolean')]
    private $activ;

    #[ORM\OneToOne(targetEntity: AuditTom::class, cascade: ['persist', 'remove'])]
    private $previous;

    /**
     * @Assert\NotBlank()
     */
    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    /**
     * @Assert\NotBlank()
     * @Encrypted()
     */
    #[ORM\Column(type: 'text')]
    private $nummer;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'auditToms')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    /**
     * @Encrypted()
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $tomAttribut;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $tomZiel;

    /**
     * @Assert\NotBlank()
     * @Encrypted()
     */
    #[ORM\Column(type: 'text')]
    private $kategorie;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'assignedAudits')]
    private $assignedUser;

    public function __construct()
    {
        $this->ziele = new ArrayCollection();
        $this->abteilung = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFrage(): ?string
    {
        return $this->frage;
    }

    public function setFrage(string $frage): self
    {
        $this->frage = $frage;

        return $this;
    }

    public function getBemerkung(): ?string
    {
        return $this->bemerkung;
    }

    public function setBemerkung(string $bemerkung): self
    {
        $this->bemerkung = $bemerkung;

        return $this;
    }

    public function getEmpfehlung(): ?string
    {
        return $this->empfehlung;
    }

    public function setEmpfehlung(string $empfehlung): self
    {
        $this->empfehlung = $empfehlung;

        return $this;
    }

    /**
     * @return Collection|AuditTomZiele[]
     */
    public function getZiele(): Collection
    {
        return $this->ziele;
    }

    public function addZiele(AuditTomZiele $ziele): self
    {
        if (!$this->ziele->contains($ziele)) {
            $this->ziele[] = $ziele;
        }

        return $this;
    }

    public function removeZiele(AuditTomZiele $ziele): self
    {
        if ($this->ziele->contains($ziele)) {
            $this->ziele->removeElement($ziele);
        }

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
     * @return Collection|AuditTomAbteilung[]
     */
    public function getAbteilung(): Collection
    {
        return $this->abteilung;
    }

    public function addAbteilung(AuditTomAbteilung $abteilung): self
    {
        if (!$this->abteilung->contains($abteilung)) {
            $this->abteilung[] = $abteilung;
        }

        return $this;
    }

    public function removeAbteilung(AuditTomAbteilung $abteilung): self
    {
        if ($this->abteilung->contains($abteilung)) {
            $this->abteilung->removeElement($abteilung);
        }

        return $this;
    }

    public function getStatus(): ?AuditTomStatus
    {
        return $this->status;
    }

    public function setStatus(?AuditTomStatus $status): self
    {
        $this->status = $status;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getNummer(): ?string
    {
        return $this->nummer;
    }

    public function setNummer(string $nummer): self
    {
        $this->nummer = $nummer;

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

    public function getTomAttribut(): ?string
    {
        return $this->tomAttribut;
    }

    public function setTomAttribut(?string $tomAttribut): self
    {
        $this->tomAttribut = $tomAttribut;

        return $this;
    }

    public function getTomZiel(): ?int
    {
        return $this->tomZiel;
    }

    public function setTomZiel(?int $tomZiel): self
    {
        $this->tomZiel = $tomZiel;

        return $this;
    }

    public function getKategorie(): ?string
    {
        return $this->kategorie;
    }

    public function setKategorie(string $kategorie): self
    {
        $this->kategorie = $kategorie;

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
}
