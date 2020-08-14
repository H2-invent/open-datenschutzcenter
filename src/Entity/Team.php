<?php

namespace App\Entity;

use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;
use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=TeamRepository::class)
 */
class Team
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
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\NotBlank()
     */
    private $activ;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="team")
     */
    private $members;

    /**
     * @ORM\OneToMany(targetEntity=AuditTomZiele::class, mappedBy="team")
     */
    private $ziele;

    /**
     * @ORM\OneToMany(targetEntity=AuditTomAbteilung::class, mappedBy="team")
     */
    private $abteilungen;

    /**
     * @ORM\OneToMany(targetEntity=AuditTom::class, mappedBy="team")
     */
    private $auditToms;

    /**
     * @ORM\OneToMany(targetEntity=Kontakte::class, mappedBy="team", orphanRemoval=true)
     */
    private $kontakte;

    /**
     * @ORM\OneToMany(targetEntity=VVT::class, mappedBy="team")
     */
    private $vvts;

    /**
     * @ORM\OneToMany(targetEntity=Datenweitergabe::class, mappedBy="team")
     */
    private $datenweitergaben;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $strasse;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $plz;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $stadt;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $telefon;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $dsb;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $ceo;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $clonedAt;

    /**
     * @ORM\ManyToMany(targetEntity=AkademieKurse::class, mappedBy="team")
     */
    private $kurse;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="akademieUser")
     */
    private $akademieUsers;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="adminUser")
     */
    private $admins;

    /**
     * @ORM\OneToMany(targetEntity=Tom::class, mappedBy="team")
     */
    private $toms;

    /**
     * @ORM\OneToMany(targetEntity=Vorfall::class, mappedBy="team")
     */
    private $vorfalls;

    /**
     * @ORM\OneToMany(targetEntity=Produkte::class, mappedBy="team")
     */
    private $produktes;

    /**
     * @ORM\OneToMany(targetEntity=Forms::class, mappedBy="team")
     */
    private $forms;

    /**
     * @ORM\OneToMany(targetEntity=Policies::class, mappedBy="team")
     */
    private $policies;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $signature;

    /**
     * @ORM\OneToMany(targetEntity=Software::class, mappedBy="team")
     */
    private $software;

    /**
     * @ORM\OneToMany(targetEntity=Task::class, mappedBy="team")
     */
    private $tasks;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Encrypted()
     */
    private $externalLink;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Encrypted()
     */
    private $video;

    /**
     * @ORM\OneToMany(targetEntity=ClientRequest::class, mappedBy="team")
     */
    private $clientRequests;

    /**
     * @ORM\Column(type="text", nullable=true, unique=true)
     */
    private $slug;


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
            $member->setTeam($this);
        }

        return $this;
    }

    public function removeMember(User $member): self
    {
        if ($this->members->contains($member)) {
            $this->members->removeElement($member);
            // set the owning side to null (unless already changed)
            if ($member->getTeam() === $this) {
                $member->setTeam(null);
            }
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
            $admin->setAdminUser($this);
        }

        return $this;
    }

    public function removeAdmin(User $admin): self
    {
        if ($this->admins->contains($admin)) {
            $this->admins->removeElement($admin);
            // set the owning side to null (unless already changed)
            if ($admin->getAdminUser() === $this) {
                $admin->setAdminUser(null);
            }
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

}
