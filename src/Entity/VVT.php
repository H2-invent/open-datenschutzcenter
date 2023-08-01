<?php

/**
 * Modified by
 * User: Jan Juister
 * Date: 13.05.2022
 */

namespace App\Entity;

use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;
use App\Repository\VVTRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VVTRepository::class)]
class VVT
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    /**
     * @Encrypted()
     */
    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private $name;

    #[ORM\Column(type: 'text', nullable: true)]
    private $verantwortlich;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private $zweck;

    #[ORM\Column(type: 'boolean')]
    private $jointControl = false;

    #[ORM\Column(type: 'boolean')]
    private $auftragsverarbeitung = false;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\NotBlank]
    private $speicherung;

    #[ORM\ManyToMany(targetEntity: VVTGrundlage::class)]
    #[Assert\NotBlank]
    private $grundlage;

    #[ORM\ManyToMany(targetEntity: VVTPersonen::class)]
    #[Assert\NotBlank]
    private $personengruppen;

    #[ORM\ManyToMany(targetEntity: VVTDatenkategorie::class, cascade: ['persist', 'remove'])]
    #[Assert\NotBlank]
    private $kategorien;

    #[ORM\Column(type: 'text', nullable: true)]
    private $weitergabe;

    #[ORM\Column(type: 'boolean')]
    private $eu = false;


    /**
     * @Encrypted()
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $tom;

    #[ORM\ManyToMany(targetEntity: VVTRisiken::class)]
    #[Assert\NotBlank]
    private $risiko;

    /**
     * @Encrypted()
     */
    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private $nummer;

    #[ORM\ManyToOne(targetEntity: VVTStatus::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private $status;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'vvts')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private $team;

    #[ORM\Column(type: 'integer')]
    private $activ;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotBlank]
    private $CreatedAt;

    #[ORM\OneToOne(targetEntity: VVT::class, cascade: ['persist', 'remove'])]
    private $previous;

    #[ORM\OneToMany(targetEntity: VVTDsfa::class, mappedBy: 'vvt')]
    private $dsfa;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'vVTs')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    /**
     * @Encrypted()
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $informationspflicht;

    /**
     * @Encrypted()
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $dsb;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank]
    private $beurteilungEintritt;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank]
    private $beurteilungSchaden;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'myVvts')]
    #[ORM\JoinColumn(nullable: false)]
    private $userContract;

    #[ORM\ManyToMany(targetEntity: Datenweitergabe::class, inversedBy: 'verfahren')]
    private $datenweitergaben;

    #[ORM\ManyToOne(targetEntity: Tom::class, inversedBy: 'vvts')]
    private $tomLink;

    #[ORM\ManyToOne(targetEntity: AuditTomAbteilung::class, inversedBy: 'vVTs')]
    private $abteilung;

    #[ORM\ManyToMany(targetEntity: Produkte::class, inversedBy: 'Vvts')]
    private $produkt;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'assignedVvts')]
    private $assignedUser;

    #[ORM\ManyToMany(targetEntity: Policies::class, mappedBy: 'processes')]
    private $policies;

    #[ORM\ManyToMany(targetEntity: Software::class, mappedBy: 'vvts')]
    private $software;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $approved;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private $approvedBy;

    #[ORM\Column(type: 'text', nullable: true)]
    private $source;

    /**
     * @Encrypted()
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $loeschfrist;

    #[ORM\Column(type: 'boolean')]
    private $inherited = false;

    public function __construct()
    {
        $this->grundlage = new ArrayCollection();
        $this->personengruppen = new ArrayCollection();
        $this->kategorien = new ArrayCollection();
        $this->risiko = new ArrayCollection();
        $this->dsfa = new ArrayCollection();
        $this->datenweitergaben = new ArrayCollection();
        $this->produkt = new ArrayCollection();
        $this->policies = new ArrayCollection();
        $this->software = new ArrayCollection();
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

    public function getVerantwortlich(): ?string
    {
        return $this->verantwortlich;
    }

    public function setVerantwortlich(?string $verantwortlich): self
    {
        $this->verantwortlich = $verantwortlich;

        return $this;
    }

    public function getZweck(): ?string
    {
        return $this->zweck;
    }

    public function setZweck(string $zweck): self
    {
        $this->zweck = $zweck;

        return $this;
    }

    public function getJointControl(): ?bool
    {
        return $this->jointControl;
    }

    public function setJointControl(bool $jointControl): self
    {
        $this->jointControl = $jointControl;

        return $this;
    }

    public function getAuftragsverarbeitung(): ?bool
    {
        return $this->auftragsverarbeitung;
    }

    public function setAuftragsverarbeitung(bool $auftragsverarbeitung): self
    {
        $this->auftragsverarbeitung = $auftragsverarbeitung;

        return $this;
    }

    public function getSpeicherung(): ?string
    {
        return $this->speicherung;
    }

    public function setSpeicherung(?string $speicherung): self
    {
        $this->speicherung = $speicherung;

        return $this;
    }

    /**
     * @return Collection|VVTGrundlage[]
     */
    public function getGrundlage(): Collection
    {
        return $this->grundlage;
    }

    public function addGrundlage(VVTGrundlage $grundlage): self
    {
        if (!$this->grundlage->contains($grundlage)) {
            $this->grundlage[] = $grundlage;
        }

        return $this;
    }

    public function removeGrundlage(VVTGrundlage $grundlage): self
    {
        if ($this->grundlage->contains($grundlage)) {
            $this->grundlage->removeElement($grundlage);
        }

        return $this;
    }

    /**
     * @return Collection|VVTPersonen[]
     */
    public function getPersonengruppen(): Collection
    {
        return $this->personengruppen;
    }

    public function addPersonengruppen(VVTPersonen $personengruppen): self
    {
        if (!$this->personengruppen->contains($personengruppen)) {
            $this->personengruppen[] = $personengruppen;
        }

        return $this;
    }

    public function removePersonengruppen(VVTPersonen $personengruppen): self
    {
        if ($this->personengruppen->contains($personengruppen)) {
            $this->personengruppen->removeElement($personengruppen);
        }

        return $this;
    }

    /**
     * @return Collection|VVTDatenkategorie[]
     */
    public function getKategorien(): Collection
    {
        return $this->kategorien;
    }

    public function addKategorien(VVTDatenkategorie $kategorien): self
    {
        if (!$this->kategorien->contains($kategorien)) {
            $this->kategorien[] = $kategorien;
        }

        return $this;
    }

    public function removeKategorien(VVTDatenkategorie $kategorien): self
    {
        if ($this->kategorien->contains($kategorien)) {
            $this->kategorien->removeElement($kategorien);
        }

        return $this;
    }

    public function getWeitergabe(): ?string
    {
        return $this->weitergabe;
    }

    public function setWeitergabe(?string $weitergabe): self
    {
        $this->weitergabe = $weitergabe;

        return $this;
    }

    public function getEu(): ?bool
    {
        return $this->eu;
    }

    public function setEu(bool $eu): self
    {
        $this->eu = $eu;

        return $this;
    }



    public function getTom(): ?string
    {
        return $this->tom;
    }

    public function setTom(?string $tom): self
    {
        $this->tom = $tom;

        return $this;
    }

    /**
     * @return Collection|VVTRisiken[]
     */
    public function getRisiko(): Collection
    {
        return $this->risiko;
    }

    public function addRisiko(VVTRisiken $risiko): self
    {
        if (!$this->risiko->contains($risiko)) {
            $this->risiko[] = $risiko;
        }

        return $this;
    }

    public function removeRisiko(VVTRisiken $risiko): self
    {
        if ($this->risiko->contains($risiko)) {
            $this->risiko->removeElement($risiko);
        }

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

    public function getStatus(): ?VVTStatus
    {
        return $this->status;
    }

    public function setStatus(?VVTStatus $status): self
    {
        $this->status = $status;

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

    public function getActiv(): ?int
    {
        return $this->activ;
    }

    public function setActiv(int $activ): self
    {
        $this->activ = $activ;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->CreatedAt;
    }

    public function setCreatedAt(\DateTimeInterface $CreatedAt): self
    {
        $this->CreatedAt = $CreatedAt;

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

    /**
     * @return Collection|VVTDsfa[]
     */
    public function getDsfa(): Collection
    {
        return $this->dsfa;
    }

    public function addDsfa(VVTDsfa $dsfa): self
    {
        if (!$this->dsfa->contains($dsfa)) {
            $this->dsfa[] = $dsfa;
            $dsfa->setVvt($this);
        }

        return $this;
    }

    public function removeDsfa(VVTDsfa $dsfa): self
    {
        if ($this->dsfa->contains($dsfa)) {
            $this->dsfa->removeElement($dsfa);
            // set the owning side to null (unless already changed)
            if ($dsfa->getVvt() === $this) {
                $dsfa->setVvt(null);
            }
        }

        return $this;
    }

    public function getActivDsfa()
    {
        foreach ($this->dsfa as $data) {
            if ($data->getActiv()) {
                return $data;
            }
        }
    }

    /**
     * @return Collection|VVTDsfa[]
     */
    public function getLatestDsfa()
    {
        return $this->createQueryBuilder('d')
            ->orderBy('d.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
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

    public function getInformationspflicht(): ?string
    {
        return $this->informationspflicht;
    }

    public function setInformationspflicht(?string $informationspflicht): self
    {
        $this->informationspflicht = $informationspflicht;

        return $this;
    }

    public function getDsb(): ?string
    {
        return $this->dsb;
    }

    public function setDsb(?string $dsb): self
    {
        $this->dsb = $dsb;

        return $this;
    }

    public function getBeurteilungEintritt(): ?int
    {
        return $this->beurteilungEintritt;
    }

    public function getBeurteilungEintrittString()
    {
        switch ($this->beurteilungEintritt) {
            case 1:
                return 'Vernachlässigbar';
                break;
            case 2:
                return 'Eingeschränk möglich';
                break;
            case 3:
                return 'Signifikant';
                break;
            case 4:
                return 'Sehr wahrscheinlich';
                break;
            default:
                return "Nicht ausgewählt";
                break;
        }
    }

    public function setBeurteilungEintritt(int $beurteilungEintritt): self
    {
        $this->beurteilungEintritt = $beurteilungEintritt;

        return $this;
    }

    public function getBeurteilungSchaden(): ?int
    {
        return $this->beurteilungSchaden;
    }

    public function getBeurteilungSchadenString()
    {
        switch ($this->beurteilungSchaden) {
            case 1:
                return 'Gering (kaum Auswirkung)';
                break;
            case 2:
                return 'Eingeschränkt vorhanden';
                break;
            case 3:
                return 'Signifikant';
                break;
            case 4:
                return 'Hoch (schwerwiegend bis existenzbedrohend)';
                break;
            default:
                return "Nicht ausgewählt";
                break;
        }
    }

    public function setBeurteilungSchaden(int $beurteilungSchaden): self
    {
        $this->beurteilungSchaden = $beurteilungSchaden;

        return $this;
    }

    public function getUserContract(): ?User
    {
        return $this->userContract;
    }

    public function setUserContract(?User $userContract): self
    {
        $this->userContract = $userContract;

        return $this;
    }

    /**
     * @return Collection|Datenweitergabe[]
     */
    public function getDatenweitergaben(): Collection
    {
        return $this->datenweitergaben;
    }

    public function addDatenweitergaben(Datenweitergabe $datenweitergaben): self
    {
        if (!$this->datenweitergaben->contains($datenweitergaben)) {
            $this->datenweitergaben[] = $datenweitergaben;
        }

        return $this;
    }

    public function removeDatenweitergaben(Datenweitergabe $datenweitergaben): self
    {
        if ($this->datenweitergaben->contains($datenweitergaben)) {
            $this->datenweitergaben->removeElement($datenweitergaben);
        }

        return $this;
    }

    public function getTomLink(): ?Tom
    {
        return $this->tomLink;
    }

    public function setTomLink(?Tom $tom): self
    {
        $this->tomLink = $tom;

        return $this;
    }

    public function getAbteilung(): ?AuditTomAbteilung
    {
        return $this->abteilung;
    }

    public function setAbteilung(?AuditTomAbteilung $abteilung): self
    {
        $this->abteilung = $abteilung;

        return $this;
    }

    /**
     * @return Collection|Produkte[]
     */
    public function getProdukt(): Collection
    {
        return $this->produkt;
    }

    public function addProdukt(Produkte $produkt): self
    {
        if (!$this->produkt->contains($produkt)) {
            $this->produkt[] = $produkt;
        }

        return $this;
    }

    public function removeProdukt(Produkte $produkt): self
    {
        if ($this->produkt->contains($produkt)) {
            $this->produkt->removeElement($produkt);
        }

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

    /**
     * @return Collection|Policies[]
     */
    public function getPolicies(): Collection
    {
        return $this->policies;
    }

    public function addPolicy(Policies $policy): self
    {
        if (!$this->policies->contains($policy)) {
            $this->policies[] = $policy;
            $policy->addProcess($this);
        }

        return $this;
    }

    public function removePolicy(Policies $policy): self
    {
        if ($this->policies->contains($policy)) {
            $this->policies->removeElement($policy);
            $policy->removeProcess($this);
        }

        return $this;
    }

    /**
     * @return Collection|Software[]
     */
    public function getSoftware(): Collection
    {
        return $this->software;
    }

    public function addSoftware(Software $software): self
    {
        if (!$this->software->contains($software)) {
            $this->software[] = $software;
            $software->addVvt($this);
        }

        return $this;
    }

    public function removeSoftware(Software $software): self
    {
        if ($this->software->contains($software)) {
            $this->software->removeElement($software);
            $software->removeVvt($this);
        }

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

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getLoeschfrist(): ?string
    {
        return $this->loeschfrist;
    }

    public function setLoeschfrist(?string $loeschfrist): self
    {
        $this->loeschfrist = $loeschfrist;

        return $this;
    }

    public function getInherited(): bool
    {
        return $this->inherited;
    }

    public function setInherited(bool $inherited): self
    {
        $this->inherited = $inherited;

        return $this;
    }

    public function __clone()
    {
        unset($this->kategorien);
        $this->kategorien = new ArrayCollection();
    }
}
