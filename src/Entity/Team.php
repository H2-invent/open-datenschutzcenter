<?php

/**
 * Modified by
 * User: Jan Juister
 * Date: 13.05.2022
 */

namespace App\Entity;

use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

#[Gedmo\Tree(type: 'nested')]
#[ORM\Entity(repositoryClass: NestedTreeRepository::class)]
#[UniqueEntity('slug')]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Assert\NotBlank]
    private $name;

    #[ORM\Column(type: 'text', nullable: true)]
    private $keycloakGroup;

    #[ORM\Column(type: 'boolean')]
    #[Assert\NotBlank]
    private $activ;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'teams')]
    private $members;

    #[ORM\OneToMany(targetEntity: AuditTomZiele::class, mappedBy: 'team')]
    private Collection $ziele;

    #[ORM\OneToMany(targetEntity: AuditTomAbteilung::class, mappedBy: 'team')]
    private Collection $abteilungen;

    #[ORM\OneToMany(targetEntity: AuditTom::class, mappedBy: 'team')]
    private Collection $auditToms;

    #[ORM\OneToMany(targetEntity: Kontakte::class, mappedBy: 'team', orphanRemoval: true)]
    private Collection $kontakte;

    #[ORM\OneToMany(targetEntity: VVT::class, mappedBy: 'team')]
    private Collection $vvts;

    #[ORM\OneToMany(targetEntity: Datenweitergabe::class, mappedBy: 'team')]
    private Collection $datenweitergaben;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private $strasse;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private $plz;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private $stadt;

    #[ORM\Column(type: 'text', nullable: true)]
    private $email;

    #[ORM\Column(type: 'text', nullable: true)]
    private $telefon;

    #[ORM\Column(type: 'text', nullable: true)]
    private $dsb;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private $ceo;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $clonedAt;

    #[ORM\ManyToMany(targetEntity: AkademieKurse::class, mappedBy: 'team')]
    private $kurse;

    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'akademieUser')]
    private Collection $akademieUsers;

    #[ORM\JoinTable(name: 'team_admin')]
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'adminRoles')]
    private $admins;

    #[ORM\OneToMany(targetEntity: Tom::class, mappedBy: 'team')]
    private Collection $toms;

    #[ORM\OneToMany(targetEntity: Vorfall::class, mappedBy: 'team')]
    private Collection $vorfalls;

    #[ORM\OneToMany(targetEntity: Produkte::class, mappedBy: 'team')]
    private Collection $produktes;

    #[ORM\OneToMany(targetEntity: Forms::class, mappedBy: 'team')]
    private Collection $forms;

    #[ORM\OneToMany(targetEntity: Policies::class, mappedBy: 'team')]
    private Collection $policies;

    #[ORM\Column(type: 'text', nullable: true)]
    private $signature;

    #[ORM\OneToMany(targetEntity: Software::class, mappedBy: 'team')]
    private Collection $software;

    #[ORM\OneToMany(targetEntity: Task::class, mappedBy: 'team')]
    private Collection $tasks;

    /**
     * @Encrypted()
     */
    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Url]
    private $externalLink;

    /**
     * @Encrypted()
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $video;

    #[ORM\OneToMany(targetEntity: ClientRequest::class, mappedBy: 'team')]
    private Collection $clientRequests;

    #[ORM\Column(type: 'text', nullable: true)]
    private $slug;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'teamDsb')]
    private $dsbUser;

    #[ORM\OneToMany(targetEntity: Report::class, mappedBy: 'team')]
    private Collection $reports;

    #[ORM\OneToMany(targetEntity: VVTDatenkategorie::class, mappedBy: 'team')]
    private Collection $vVTDatenkategories;

    #[ORM\OneToMany(targetEntity: VVTPersonen::class, mappedBy: 'team')]
    private Collection $vVTPersonens;

    #[ORM\OneToMany(targetEntity: VVTRisiken::class, mappedBy: 'team')]
    private Collection $vVTRisikens;

    #[ORM\OneToMany(targetEntity: VVTGrundlage::class, mappedBy: 'team')]
    private Collection $vVTGrundlages;

    #[ORM\Column(type: 'text', nullable: true)]
    private $industry;

    #[ORM\Column(type: 'text', nullable: true)]
    private $specialty;

    #[ORM\OneToMany(mappedBy: 'team', targetEntity: VVTStatus::class)]
    private Collection $vVTStatuses;

    #[ORM\OneToMany(mappedBy: 'team', targetEntity: DatenweitergabeGrundlagen::class)]
    private Collection $datenweitergabeGrundlagens;

    #[ORM\OneToMany(mappedBy: 'team', targetEntity: DatenweitergabeStand::class)]
    private Collection $datenweitergabeStands;

    #[ORM\OneToMany(mappedBy: 'team', targetEntity: Loeschkonzept::class)]
    private Collection $loeschkonzepts;

    #[ORM\OneToMany(mappedBy: 'team', targetEntity: Questionnaire::class)]
    private Collection $questionnaires;

    #[Gedmo\TreeLevel]
    #[ORM\Column(name: 'lvl', type: 'integer', nullable: true)]
    private $lvl;

    #[Gedmo\TreeRoot]
    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: 'tree_root', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    private $root;

    #[Gedmo\TreeParent]
    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $parent;

    #[Gedmo\TreeLeft]
    #[ORM\Column(name: 'lft', type: 'integer', nullable: true)]
    private $lft;

    #[Gedmo\TreeRight]
    #[ORM\Column(name: 'rgt', type: 'integer', nullable: true)]
    private $rgt;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: Team::class)]
    private $children;

    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->ziele = new ArrayCollection();
        $this->abteilungen = new ArrayCollection();
        $this->auditToms = new ArrayCollection();
        $this->kontakte = new ArrayCollection();
        $this->vvts = new ArrayCollection();
        $this->datenweitergaben = new ArrayCollection();
        $this->kurse = new ArrayCollection();
        $this->akademieUsers = new ArrayCollection();
        $this->admins = new ArrayCollection();
        $this->toms = new ArrayCollection();
        $this->vorfalls = new ArrayCollection();
        $this->produktes = new ArrayCollection();
        $this->forms = new ArrayCollection();
        $this->policies = new ArrayCollection();
        $this->software = new ArrayCollection();
        $this->tasks = new ArrayCollection();
        $this->clientRequests = new ArrayCollection();
        $this->reports = new ArrayCollection();
        $this->vVTDatenkategories = new ArrayCollection();
        $this->vVTPersonens = new ArrayCollection();
        $this->vVTRisikens = new ArrayCollection();
        $this->vVTGrundlages = new ArrayCollection();
        $this->vVTStatuses = new ArrayCollection();
        $this->datenweitergabeGrundlagens = new ArrayCollection();
        $this->datenweitergabeStands = new ArrayCollection();
        $this->loeschkonzepts = new ArrayCollection();
        $this->questionnaires = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
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

    public function getKeycloakGroup(): ?string
    {
        return $this->keycloakGroup ? : $this->name;
    }

    public function setKeycloakGroup(?string $keycloakGroup): self
    {
        $this->keycloakGroup = $keycloakGroup;

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

    /**
     * @return Collection|User[]
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(User $member): self
    {
        if (!$this->members->contains($member)) {
            $this->members[] = $member;
            $member->addTeam($this);
        }

        return $this;
    }

    public function removeMember(User $member): self
    {
        if ($this->members->contains($member)) {
            $this->members->removeElement($member);
            // set the owning side to null (unless already changed)
            $member->removeTeam($this);
        }

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
            $ziele->setTeam($this);
        }

        return $this;
    }

    public function removeZiele(AuditTomZiele $ziele): self
    {
        if ($this->ziele->contains($ziele)) {
            $this->ziele->removeElement($ziele);
            // set the owning side to null (unless already changed)
            if ($ziele->getTeam() === $this) {
                $ziele->setTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|AuditTomAbteilung[]
     */
    public function getAbteilungen(): Collection
    {
        return $this->abteilungen;
    }

    public function addAbteilungen(AuditTomAbteilung $abteilungen): self
    {
        if (!$this->abteilungen->contains($abteilungen)) {
            $this->abteilungen[] = $abteilungen;
            $abteilungen->setTeam($this);
        }

        return $this;
    }

    public function removeAbteilungen(AuditTomAbteilung $abteilungen): self
    {
        if ($this->abteilungen->contains($abteilungen)) {
            $this->abteilungen->removeElement($abteilungen);
            // set the owning side to null (unless already changed)
            if ($abteilungen->getTeam() === $this) {
                $abteilungen->setTeam(null);
            }
        }

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
            $auditTom->setTeam($this);
        }

        return $this;
    }

    public function removeAuditTom(AuditTom $auditTom): self
    {
        if ($this->auditToms->contains($auditTom)) {
            $this->auditToms->removeElement($auditTom);
            // set the owning side to null (unless already changed)
            if ($auditTom->getTeam() === $this) {
                $auditTom->setTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Kontakte[]
     */
    public function getKontakte(): Collection
    {
        return $this->kontakte;
    }

    public function addKontakte(Kontakte $kontakte): self
    {
        if (!$this->kontakte->contains($kontakte)) {
            $this->kontakte[] = $kontakte;
            $kontakte->setTeam($this);
        }

        return $this;
    }

    public function removeKontakte(Kontakte $kontakte): self
    {
        if ($this->kontakte->contains($kontakte)) {
            $this->kontakte->removeElement($kontakte);
            // set the owning side to null (unless already changed)
            if ($kontakte->getTeam() === $this) {
                $kontakte->setTeam(null);
            }
        }

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
            $vvt->setTeam($this);
        }

        return $this;
    }

    public function removeVvt(VVT $vvt): self
    {
        if ($this->vvts->contains($vvt)) {
            $this->vvts->removeElement($vvt);
            // set the owning side to null (unless already changed)
            if ($vvt->getTeam() === $this) {
                $vvt->setTeam(null);
            }
        }

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
            $datenweitergaben->setTeam($this);
        }

        return $this;
    }

    public function removeDatenweitergaben(Datenweitergabe $datenweitergaben): self
    {
        if ($this->datenweitergaben->contains($datenweitergaben)) {
            $this->datenweitergaben->removeElement($datenweitergaben);
            // set the owning side to null (unless already changed)
            if ($datenweitergaben->getTeam() === $this) {
                $datenweitergaben->setTeam(null);
            }
        }

        return $this;
    }

    public function getStrasse(): ?string
    {
        return $this->strasse;
    }

    public function setStrasse(string $strasse): self
    {
        $this->strasse = $strasse;

        return $this;
    }

    public function getPlz(): ?string
    {
        return $this->plz;
    }

    public function setPlz(string $plz): self
    {
        $this->plz = $plz;

        return $this;
    }

    public function getStadt(): ?string
    {
        return $this->stadt;
    }

    public function setStadt(string $stadt): self
    {
        $this->stadt = $stadt;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getTelefon(): ?string
    {
        return $this->telefon;
    }

    public function setTelefon(?string $telefon): self
    {
        $this->telefon = $telefon;

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

    public function getCeo(): ?string
    {
        return $this->ceo;
    }

    public function setCeo(string $ceo): self
    {
        $this->ceo = $ceo;

        return $this;
    }

    public function getClonedAt(): ?\DateTimeInterface
    {
        return $this->clonedAt;
    }

    public function setClonedAt(?\DateTimeInterface $clonedAt): self
    {
        $this->clonedAt = $clonedAt;

        return $this;
    }

    /**
     * @return Collection|AkademieKurse[]
     */
    public function getKurse(): Collection
    {
        return $this->kurse;
    }

    public function addKurse(AkademieKurse $kurse): self
    {
        if (!$this->kurse->contains($kurse)) {
            $this->kurse[] = $kurse;
            $kurse->addTeam($this);
        }

        return $this;
    }

    public function removeKurse(AkademieKurse $kurse): self
    {
        if ($this->kurse->contains($kurse)) {
            $this->kurse->removeElement($kurse);
            $kurse->removeTeam($this);
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getAkademieUsers(): Collection
    {
        return $this->akademieUsers;
    }

    public function addAkademieUser(User $akademieUser): self
    {
        if (!$this->akademieUsers->contains($akademieUser)) {
            $this->akademieUsers[] = $akademieUser;
            $akademieUser->setAkademieUser($this);
        }

        return $this;
    }

    public function removeAkademieUser(User $akademieUser): self
    {
        if ($this->akademieUsers->contains($akademieUser)) {
            $this->akademieUsers->removeElement($akademieUser);
            // set the owning side to null (unless already changed)
            if ($akademieUser->getAkademieUser() === $this) {
                $akademieUser->setAkademieUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getAdmins(): Collection
    {
        return $this->admins;
    }

    public function addAdmin(User $admin): self
    {
        if (!$this->admins->contains($admin)) {
            $this->admins[] = $admin;
        }

        return $this;
    }

    public function removeAdmin(User $admin): self
    {
        if ($this->admins->contains($admin)) {
            $this->admins->removeElement($admin);
        }

        return $this;
    }

    /**
     * @return Collection|Tom[]
     */
    public function getToms(): Collection
    {
        return $this->toms;
    }

    public function addTom(Tom $tom): self
    {
        if (!$this->toms->contains($tom)) {
            $this->toms[] = $tom;
            $tom->setTeam($this);
        }

        return $this;
    }

    public function removeTom(Tom $tom): self
    {
        if ($this->toms->contains($tom)) {
            $this->toms->removeElement($tom);
            // set the owning side to null (unless already changed)
            if ($tom->getTeam() === $this) {
                $tom->setTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Vorfall[]
     */
    public function getVorfalls(): Collection
    {
        return $this->vorfalls;
    }

    public function addVorfall(Vorfall $vorfall): self
    {
        if (!$this->vorfalls->contains($vorfall)) {
            $this->vorfalls[] = $vorfall;
            $vorfall->setTeam($this);
        }

        return $this;
    }

    public function removeVorfall(Vorfall $vorfall): self
    {
        if ($this->vorfalls->contains($vorfall)) {
            $this->vorfalls->removeElement($vorfall);
            // set the owning side to null (unless already changed)
            if ($vorfall->getTeam() === $this) {
                $vorfall->setTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Produkte[]
     */
    public function getProduktes(): Collection
    {
        return $this->produktes;
    }

    public function addProdukte(Produkte $produkte): self
    {
        if (!$this->produktes->contains($produkte)) {
            $this->produktes[] = $produkte;
            $produkte->setTeam($this);
        }

        return $this;
    }

    public function removeProdukte(Produkte $produkte): self
    {
        if ($this->produktes->contains($produkte)) {
            $this->produktes->removeElement($produkte);
            // set the owning side to null (unless already changed)
            if ($produkte->getTeam() === $this) {
                $produkte->setTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Forms[]
     */
    public function getForms(): Collection
    {
        return $this->forms;
    }

    public function addForm(Forms $form): self
    {
        if (!$this->forms->contains($form)) {
            $this->forms[] = $form;
            $form->setTeam($this);
        }

        return $this;
    }

    public function removeForm(Forms $form): self
    {
        if ($this->forms->contains($form)) {
            $this->forms->removeElement($form);
            // set the owning side to null (unless already changed)
            if ($form->getTeam() === $this) {
                $form->setTeam(null);
            }
        }

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
            $policy->setTeam($this);
        }

        return $this;
    }

    public function removePolicy(Policies $policy): self
    {
        if ($this->policies->contains($policy)) {
            $this->policies->removeElement($policy);
            // set the owning side to null (unless already changed)
            if ($policy->getTeam() === $this) {
                $policy->setTeam(null);
            }
        }

        return $this;
    }

    public function getSignature(): ?string
    {
        return $this->signature;
    }

    public function setSignature(?string $signature): self
    {
        $this->signature = $signature;

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
            $software->setTeam($this);
        }

        return $this;
    }

    public function removeSoftware(Software $software): self
    {
        if ($this->software->contains($software)) {
            $this->software->removeElement($software);
            // set the owning side to null (unless already changed)
            if ($software->getTeam() === $this) {
                $software->setTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Task[]
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): self
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks[] = $task;
            $task->setTeam($this);
        }

        return $this;
    }

    public function removeTask(Task $task): self
    {
        if ($this->tasks->contains($task)) {
            $this->tasks->removeElement($task);
            // set the owning side to null (unless already changed)
            if ($task->getTeam() === $this) {
                $task->setTeam(null);
            }
        }

        return $this;
    }

    public function getExternalLink(): ?string
    {
        return $this->externalLink;
    }

    public function setExternalLink(?string $externalLink): self
    {
        $this->externalLink = $externalLink;

        return $this;
    }

    public function getVideo(): ?string
    {
        return $this->video;
    }

    public function setVideo(?string $video): self
    {
        $this->video = $video;

        return $this;
    }

    /**
     * @return Collection|ClientRequest[]
     */
    public function getClientRequests(): Collection
    {
        return $this->clientRequests;
    }

    public function addClientRequest(ClientRequest $clientRequest): self
    {
        if (!$this->clientRequests->contains($clientRequest)) {
            $this->clientRequests[] = $clientRequest;
            $clientRequest->setTeam($this);
        }

        return $this;
    }

    public function removeClientRequest(ClientRequest $clientRequest): self
    {
        if ($this->clientRequests->contains($clientRequest)) {
            $this->clientRequests->removeElement($clientRequest);
            // set the owning side to null (unless already changed)
            if ($clientRequest->getTeam() === $this) {
                $clientRequest->setTeam(null);
            }
        }

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getDsbUser(): ?User
    {
        return $this->dsbUser;
    }

    public function setDsbUser(?User $dsbUser): self
    {
        $this->dsbUser = $dsbUser;

        return $this;
    }

    /**
     * @return Collection|Report[]
     */
    public function getReports(): Collection
    {
        return $this->reports;
    }

    public function addReport(Report $report): self
    {
        if (!$this->reports->contains($report)) {
            $this->reports[] = $report;
            $report->setTeam($this);
        }

        return $this;
    }

    public function removeReport(Report $report): self
    {
        if ($this->reports->contains($report)) {
            $this->reports->removeElement($report);
            // set the owning side to null (unless already changed)
            if ($report->getTeam() === $this) {
                $report->setTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|VVTDatenkategorie[]
     */
    public function getVVTDatenkategories(): Collection
    {
        return $this->vVTDatenkategories;
    }

    public function addVVTDatenkategory(VVTDatenkategorie $vVTDatenkategory): self
    {
        if (!$this->vVTDatenkategories->contains($vVTDatenkategory)) {
            $this->vVTDatenkategories[] = $vVTDatenkategory;
            $vVTDatenkategory->setTeam($this);
        }

        return $this;
    }

    public function removeVVTDatenkategory(VVTDatenkategorie $vVTDatenkategory): self
    {
        if ($this->vVTDatenkategories->removeElement($vVTDatenkategory)) {
            // set the owning side to null (unless already changed)
            if ($vVTDatenkategory->getTeam() === $this) {
                $vVTDatenkategory->setTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|VVTPersonen[]
     */
    public function getVVTPersonens(): Collection
    {
        return $this->vVTPersonens;
    }

    public function addVVTPersonen(VVTPersonen $vVTPersonen): self
    {
        if (!$this->vVTPersonens->contains($vVTPersonen)) {
            $this->vVTPersonens[] = $vVTPersonen;
            $vVTPersonen->setTeam($this);
        }

        return $this;
    }

    public function removeVVTPersonen(VVTPersonen $vVTPersonen): self
    {
        if ($this->vVTPersonens->removeElement($vVTPersonen)) {
            // set the owning side to null (unless already changed)
            if ($vVTPersonen->getTeam() === $this) {
                $vVTPersonen->setTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|VVTRisiken[]
     */
    public function getVVTRisikens(): Collection
    {
        return $this->vVTRisikens;
    }

    public function addVVTRisiken(VVTRisiken $vVTRisiken): self
    {
        if (!$this->vVTRisikens->contains($vVTRisiken)) {
            $this->vVTRisikens[] = $vVTRisiken;
            $vVTRisiken->setTeam($this);
        }

        return $this;
    }

    public function removeVVTRisiken(VVTRisiken $vVTRisiken): self
    {
        if ($this->vVTRisikens->removeElement($vVTRisiken)) {
            // set the owning side to null (unless already changed)
            if ($vVTRisiken->getTeam() === $this) {
                $vVTRisiken->setTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|VVTGrundlage[]
     */
    public function getVVTGrundlages(): Collection
    {
        return $this->vVTGrundlages;
    }

    public function addVVTGrundlage(VVTGrundlage $vVTGrundlage): self
    {
        if (!$this->vVTGrundlages->contains($vVTGrundlage)) {
            $this->vVTGrundlages[] = $vVTGrundlage;
            $vVTGrundlage->setTeam($this);
        }

        return $this;
    }

    public function removeVVTGrundlage(VVTGrundlage $vVTGrundlage): self
    {
        if ($this->vVTGrundlages->removeElement($vVTGrundlage)) {
            // set the owning side to null (unless already changed)
            if ($vVTGrundlage->getTeam() === $this) {
                $vVTGrundlage->setTeam(null);
            }
        }

        return $this;
    }

    public function getIndustry(): ?string
    {
        return $this->industry;
    }

    public function setIndustry(?string $industry): self
    {
        $this->industry = $industry;

        return $this;
    }

    public function getSpecialty(): ?string
    {
        return $this->specialty;
    }

    public function setSpecialty(?string $specialty): self
    {
        $this->specialty = $specialty;

        return $this;
    }

    /**
     * @return Collection|VVTStatus[]
     */
    public function getVVTStatuses(): Collection
    {
        return $this->vVTStatuses;
    }

    public function addVVTStatus(VVTStatus $vVTStatus): self
    {
        if (!$this->vVTStatuses->contains($vVTStatus)) {
            $this->vVTStatuses[] = $vVTStatus;
            $vVTStatus->setTeam($this);
        }

        return $this;
    }

    public function removeVVTStatus(VVTStatus $vVTStatus): self
    {
        if ($this->vVTStatuses->removeElement($vVTStatus)) {
            // set the owning side to null (unless already changed)
            if ($vVTStatus->getTeam() === $this) {
                $vVTStatus->setTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|DatenweitergabeGrundlagen[]
     */
    public function getDatenweitergabeGrundlagens(): Collection
    {
        return $this->datenweitergabeGrundlagens;
    }

    public function addDatenweitergabeGrundlagen(DatenweitergabeGrundlagen $datenweitergabeGrundlagen): self
    {
        if (!$this->datenweitergabeGrundlagens->contains($datenweitergabeGrundlagen)) {
            $this->datenweitergabeGrundlagens[] = $datenweitergabeGrundlagen;
            $datenweitergabeGrundlagen->setTeam($this);
        }

        return $this;
    }

    public function removeDatenweitergabeGrundlagen(DatenweitergabeGrundlagen $datenweitergabeGrundlagen): self
    {
        if ($this->datenweitergabeGrundlagens->removeElement($datenweitergabeGrundlagen)) {
            // set the owning side to null (unless already changed)
            if ($datenweitergabeGrundlagen->getTeam() === $this) {
                $datenweitergabeGrundlagen->setTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|DatenweitergabeStand[]
     */
    public function getDatenweitergabeStands(): Collection
    {
        return $this->datenweitergabeStands;
    }

    public function addDatenweitergabeStand(DatenweitergabeStand $datenweitergabeStand): self
    {
        if (!$this->datenweitergabeStands->contains($datenweitergabeStand)) {
            $this->datenweitergabeStands[] = $datenweitergabeStand;
            $datenweitergabeStand->setTeam($this);
        }

        return $this;
    }

    public function removeDatenweitergabeStand(DatenweitergabeStand $datenweitergabeStand): self
    {
        if ($this->datenweitergabeStands->removeElement($datenweitergabeStand)) {
            // set the owning side to null (unless already changed)
            if ($datenweitergabeStand->getTeam() === $this) {
                $datenweitergabeStand->setTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Loeschkonzept>
     */
    public function getLoeschkonzepts(): Collection
    {
        return $this->loeschkonzepts;
    }

    public function addLoeschkonzept(Loeschkonzept $loeschkonzept): self
    {
        if (!$this->loeschkonzepts->contains($loeschkonzept)) {
            $this->loeschkonzepts[] = $loeschkonzept;
            $loeschkonzept->setTeam($this);
        }

        return $this;
    }

    public function removeLoeschkonzept(Loeschkonzept $loeschkonzept): self
    {
        if ($this->loeschkonzepts->removeElement($loeschkonzept)) {
            // set the owning side to null (unless already changed)
            if ($loeschkonzept->getTeam() === $this) {
                $loeschkonzept->setTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Questionnaire>
     */
    public function getQuestionnaires(): Collection
    {
        return $this->questionnaires;
    }

    public function addQuestionnaire(Questionnaire $questionnaire): self
    {
        if (!$this->questionnaires->contains($questionnaire)) {
            $this->questionnaires[] = $questionnaire;
            $questionnaire->setTeam($this);
        }

        return $this;
    }

    public function removeQuestionnaire(Questionnaire $questionnaire): self
    {
        if ($this->questionnaires->removeElement($questionnaire)) {
            // set the owning side to null (unless already changed)
            if ($questionnaire->getTeam() === $this) {
                $questionnaire->setTeam(null);
            }
        }

        return $this;
    }

    /**
     * returns list of elements that prevent team deletion
     * @return array
     */
    public function getDeleteBlockers(): array
    {
        $blockers = [];
        if (count($this->ziele)) $blockers[] = 'auditGoals';
        if (count($this->abteilungen)) $blockers[] = 'departments';
        if (count($this->auditToms)) $blockers[] = 'audits';
        if (count($this->kontakte)) $blockers[] = 'contacts';
        if (count($this->vvts)) $blockers[] = 'processingActivities';
        if (count($this->datenweitergaben)) $blockers[] = 'dataTransfers';
        if (count($this->kurse)) $blockers[] = 'academyCourses';
        if (count($this->akademieUsers)) $blockers[] = 'academyUsers';
        if (count($this->toms)) $blockers[] = 'toms';
        if (count($this->vorfalls)) $blockers[] = 'incidents';
        if (count($this->produktes)) $blockers[] = 'products';
        if (count($this->forms)) $blockers[] = 'forms';
        if (count($this->policies)) $blockers[] = 'policies';
        if (count($this->software)) $blockers[] = 'software';
        if (count($this->tasks)) $blockers[] = 'tasks';
        if (count($this->clientRequests)) $blockers[] = 'clientRequests';
        if (count($this->reports)) $blockers[] = 'reports';
        if (count($this->vVTDatenkategories)) $blockers[] = 'dataCategories';
        if (count($this->vVTPersonens)) $blockers[] = 'groupsOfPeople';
        if (count($this->vVTRisikens)) $blockers[] = 'processingRisks';
        if (count($this->vVTGrundlages)) $blockers[] = 'dataTransferBases';
        if (count($this->vVTStatuses)) $blockers[] = 'processingStatuses';
        if (count($this->datenweitergabeStands)) $blockers[] = 'dataTransferStatuses';
        if (count($this->loeschkonzepts)) $blockers[] = 'deleteConcepts';
        return $blockers;
    }

    public function getRoot(): ?self
    {
        return $this->root;
    }

    public function setParent(self $parent = null): void
    {
        $this->parent = $parent;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function getChildren(): ?Collection
    {
        return $this->children;
    }
}
