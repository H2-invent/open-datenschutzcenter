<?php

namespace App\Entity;

use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;
use App\Repository\TomRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TomRepository::class)]
class Tom
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    /**
     * @Assert\NotBlank()
     */
    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'toms')]
    #[ORM\JoinColumn(nullable: false)]
    private $team;

    /**
     * @Assert\NotBlank()
     */
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'toms')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    /**
     * @Assert\NotBlank()
     */
    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    #[ORM\OneToMany(targetEntity: VVT::class, mappedBy: 'tomLink')]
    private $vvts;

    #[ORM\Column(type: 'integer')]
    private $activ;

    /**
     * @Assert\NotBlank()
     * @Encrypted()
     */
    #[ORM\Column(type: 'text')]
    private $tomPseudo;

    /**
     * @Assert\NotBlank()
     * @Encrypted()
     */
    #[ORM\Column(type: 'text')]
    private $tomZutrittskontrolle;

    /**
     * @Assert\NotBlank()
     * @Encrypted()
     */
    #[ORM\Column(type: 'text')]
    private $tomZugangskontrolle;

    /**
     * @Assert\NotBlank()
     * @Encrypted()
     */
    #[ORM\Column(type: 'text')]
    private $tomZugriffskontrolle;

    /**
     * @Assert\NotBlank()
     * @Encrypted()
     */
    #[ORM\Column(type: 'text')]
    private $tomBenutzerkontrolle;

    /**
     * @Assert\NotBlank()
     * @Encrypted()
     */
    #[ORM\Column(type: 'text')]
    private $tomSpeicherkontrolle;

    /**
     * @Assert\NotBlank()
     * @Encrypted()
     */
    #[ORM\Column(type: 'text')]
    private $tomTrennbarkeit;

    /**
     * @Assert\NotBlank()
     * @Encrypted()
     */
    #[ORM\Column(type: 'text')]
    private $tomDatenintegritaet;

    /**
     * @Assert\NotBlank()
     * @Encrypted()
     */
    #[ORM\Column(type: 'text')]
    private $tomTransportkontrolle;

    /**
     * @Assert\NotBlank()
     * @Encrypted()
     */
    #[ORM\Column(type: 'text')]
    private $tomUebertragungskontrolle;

    /**
     * @Assert\NotBlank()
     * @Encrypted()
     */
    #[ORM\Column(type: 'text')]
    private $tomZuverlaessigkeit;

    /**
     * @Assert\NotBlank()
     * @Encrypted()
     */
    #[ORM\Column(type: 'text')]
    private $tomAuftragskontrolle;

    /**
     * @Assert\NotBlank()
     * @Encrypted()
     */
    #[ORM\Column(type: 'text')]
    private $tomVerfuegbarkeitskontrolle;

    /**
     * @Assert\NotBlank()
     * @Encrypted()
     */
    #[ORM\Column(type: 'text')]
    private $tomWiederherstellbarkeit;

    /**
     * @Assert\NotBlank()
     * @Encrypted()
     */
    #[ORM\Column(type: 'text')]
    private $tomAudit;

    /**
     * @Assert\NotBlank()
     * @Encrypted()
     */
    #[ORM\Column(type: 'text')]
    private $tomEingabekontrolle;

    #[ORM\OneToOne(targetEntity: Tom::class, cascade: ['persist', 'remove'])]
    private $previous;

    /**
     * @Assert\NotBlank()
     * @Encrypted()
     */
    #[ORM\Column(type: 'text')]
    private $beschreibung;

    /**
     * @Assert\NotBlank()
     * @Encrypted()
     */
    #[ORM\Column(type: 'text')]
    private $titel;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $approved;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private $approvedBy;

    public function __construct()
    {
        $this->vvts = new ArrayCollection();
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

    /**
     * @return Collection|VVT[]
     */
    public function getVvts(): Collection
    {
        return $this->vvts;
    }

    public function addVvt(VVT $vvt): self
    {
        if (!$this->vvts->contains($vvt)) {
            $this->vvts[] = $vvt;
            $vvt->setYes($this);
        }

        return $this;
    }

    public function removeVvt(VVT $vvt): self
    {
        if ($this->vvts->contains($vvt)) {
            $this->vvts->removeElement($vvt);
            // set the owning side to null (unless already changed)
            if ($vvt->getYes() === $this) {
                $vvt->setYes(null);
            }
        }

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

    public function getTomPseudo(): ?string
    {
        return $this->tomPseudo;
    }

    public function setTomPseudo(string $tomPseudo): self
    {
        $this->tomPseudo = $tomPseudo;

        return $this;
    }

    public function getTomZutrittskontrolle(): ?string
    {
        return $this->tomZutrittskontrolle;
    }

    public function setTomZutrittskontrolle(string $tomZutrittskontrolle): self
    {
        $this->tomZutrittskontrolle = $tomZutrittskontrolle;

        return $this;
    }

    public function getTomZugangskontrolle(): ?string
    {
        return $this->tomZugangskontrolle;
    }

    public function setTomZugangskontrolle(string $tomZugangskontrolle): self
    {
        $this->tomZugangskontrolle = $tomZugangskontrolle;

        return $this;
    }

    public function getTomZugriffskontrolle(): ?string
    {
        return $this->tomZugriffskontrolle;
    }

    public function setTomZugriffskontrolle(string $tomZugriffskontrolle): self
    {
        $this->tomZugriffskontrolle = $tomZugriffskontrolle;

        return $this;
    }

    public function getTomBenutzerkontrolle(): ?string
    {
        return $this->tomBenutzerkontrolle;
    }

    public function setTomBenutzerkontrolle(string $tomBenutzerkontrolle): self
    {
        $this->tomBenutzerkontrolle = $tomBenutzerkontrolle;

        return $this;
    }

    public function getTomSpeicherkontrolle(): ?string
    {
        return $this->tomSpeicherkontrolle;
    }

    public function setTomSpeicherkontrolle(string $tomSpeicherkontrolle): self
    {
        $this->tomSpeicherkontrolle = $tomSpeicherkontrolle;

        return $this;
    }

    public function getTomTrennbarkeit(): ?string
    {
        return $this->tomTrennbarkeit;
    }

    public function setTomTrennbarkeit(string $tomTrennbarkeit): self
    {
        $this->tomTrennbarkeit = $tomTrennbarkeit;

        return $this;
    }

    public function getTomDatenintegritaet(): ?string
    {
        return $this->tomDatenintegritaet;
    }

    public function setTomDatenintegritaet(string $tomDatenintegritaet): self
    {
        $this->tomDatenintegritaet = $tomDatenintegritaet;

        return $this;
    }

    public function getTomTransportkontrolle(): ?string
    {
        return $this->tomTransportkontrolle;
    }

    public function setTomTransportkontrolle(string $tomTransportkontrolle): self
    {
        $this->tomTransportkontrolle = $tomTransportkontrolle;

        return $this;
    }

    public function getTomUebertragungskontrolle(): ?string
    {
        return $this->tomUebertragungskontrolle;
    }

    public function setTomUebertragungskontrolle(string $tomUebertragungskontrolle): self
    {
        $this->tomUebertragungskontrolle = $tomUebertragungskontrolle;

        return $this;
    }

    public function getTomZuverlaessigkeit(): ?string
    {
        return $this->tomZuverlaessigkeit;
    }

    public function setTomZuverlaessigkeit(string $tomZuverlaessigkeit): self
    {
        $this->tomZuverlaessigkeit = $tomZuverlaessigkeit;

        return $this;
    }

    public function getTomAuftragskontrolle(): ?string
    {
        return $this->tomAuftragskontrolle;
    }

    public function setTomAuftragskontrolle(string $tomAuftragskontrolle): self
    {
        $this->tomAuftragskontrolle = $tomAuftragskontrolle;

        return $this;
    }

    public function getTomVerfuegbarkeitskontrolle(): ?string
    {
        return $this->tomVerfuegbarkeitskontrolle;
    }

    public function setTomVerfuegbarkeitskontrolle(string $tomVerfuegbarkeitskontrolle): self
    {
        $this->tomVerfuegbarkeitskontrolle = $tomVerfuegbarkeitskontrolle;

        return $this;
    }

    public function getTomWiederherstellbarkeit(): ?string
    {
        return $this->tomWiederherstellbarkeit;
    }

    public function setTomWiederherstellbarkeit(string $tomWiederherstellbarkeit): self
    {
        $this->tomWiederherstellbarkeit = $tomWiederherstellbarkeit;

        return $this;
    }

    public function getTomAudit(): ?string
    {
        return $this->tomAudit;
    }

    public function setTomAudit(string $tomAudit): self
    {
        $this->tomAudit = $tomAudit;

        return $this;
    }

    public function getTomEingabekontrolle(): ?string
    {
        return $this->tomEingabekontrolle;
    }

    public function setTomEingabekontrolle(string $tomEingabekontrolle): self
    {
        $this->tomEingabekontrolle = $tomEingabekontrolle;

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

    public function getBeschreibung(): ?string
    {
        return $this->beschreibung;
    }

    public function setBeschreibung(string $beschreibung): self
    {
        $this->beschreibung = $beschreibung;

        return $this;
    }

    public function getTitel(): ?string
    {
        return $this->titel;
    }

    public function setTitel(string $titel): self
    {
        $this->titel = $titel;

        return $this;
    }

    public function getApproved(): ?bool
    {
        return $this->approved;
    }

    public function setApproved(?bool $approved): self
    {
        $this->approved = $approved;

        return $this;
    }

    public function getApprovedBy(): ?User
    {
        return $this->approvedBy;
    }

    public function setApprovedBy(?User $approvedBy): self
    {
        $this->approvedBy = $approvedBy;

        return $this;
    }
}
