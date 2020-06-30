<?php
// src/Entity/User.php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Assert\NotBlank(message="fos_user.password.blank", groups={"Registration", "ResetPassword", "ChangePassword"})
     * @Assert\Length(min=8,
     *     minMessage="fos_user.password.short",
     *     groups={"Registration", "Profile", "ResetPassword", "ChangePassword"})
     */
    protected $plainPassword;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="members")
     */
    private $team;

    /**
     * @ORM\OneToMany(targetEntity=Datenweitergabe::class, mappedBy="user")
     */
    private $datenweitergabes;

    /**
     * @ORM\OneToMany(targetEntity=VVT::class, mappedBy="user")
     */
    private $vVTs;

    /**
     * @ORM\OneToMany(targetEntity=AuditTom::class, mappedBy="user")
     */
    private $auditToms;

    /**
     * @ORM\OneToMany(targetEntity=VVTDsfa::class, mappedBy="user")
     */
    private $vVTDsfas;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="akademieUsers")
     */
    private $akademieUser;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="admins")
     */
    private $adminUser;

    /**
     * @ORM\OneToMany(targetEntity=VVT::class, mappedBy="userContract")
     */
    private $myVvts;

    /**
     * @ORM\OneToMany(targetEntity=Tom::class, mappedBy="user")
     */
    private $toms;

    /**
     * @ORM\OneToMany(targetEntity=Vorfall::class, mappedBy="user")
     */
    private $vorfalls;

    /**
     * @ORM\OneToMany(targetEntity=AkademieKurse::class, mappedBy="user")
     */
    private $akademieKurses;

    /**
     * @ORM\OneToMany(targetEntity=VVT::class, mappedBy="assignedUser")
     */
    private $assignedVvts;

    /**
     * @ORM\OneToMany(targetEntity=AuditTom::class, mappedBy="assignedUser")
     */
    private $assignedAudits;

    /**
     * @ORM\OneToMany(targetEntity=Datenweitergabe::class, mappedBy="assignedUser")
     */
    private $assignedDatenweitergaben;

    /**
     * @ORM\OneToMany(targetEntity=VVTDsfa::class, mappedBy="assignedUser")
     */
    private $assignedDsfa;


    public function __construct()
    {
        parent::__construct();
        $this->datenweitergabes = new ArrayCollection();
        $this->vVTs = new ArrayCollection();
        $this->auditToms = new ArrayCollection();
        $this->vVTDsfas = new ArrayCollection();
        $this->myVvts = new ArrayCollection();
        $this->toms = new ArrayCollection();
        $this->vorfalls = new ArrayCollection();
        $this->akademieKurses = new ArrayCollection();
        $this->assignedVvts = new ArrayCollection();
        $this->assignedAudits = new ArrayCollection();
        $this->assignedDatenweitergaben = new ArrayCollection();
        $this->assignedDsfa = new ArrayCollection();
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
     * @return Collection|Datenweitergabe[]
     */
    public function getDatenweitergabes(): Collection
    {
        return $this->datenweitergabes;
    }

    public function addDatenweitergabe(Datenweitergabe $datenweitergabe): self
    {
        if (!$this->datenweitergabes->contains($datenweitergabe)) {
            $this->datenweitergabes[] = $datenweitergabe;
            $datenweitergabe->setUser($this);
        }

        return $this;
    }

    public function removeDatenweitergabe(Datenweitergabe $datenweitergabe): self
    {
        if ($this->datenweitergabes->contains($datenweitergabe)) {
            $this->datenweitergabes->removeElement($datenweitergabe);
            // set the owning side to null (unless already changed)
            if ($datenweitergabe->getUser() === $this) {
                $datenweitergabe->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|VVT[]
     */
    public function getVVTs(): Collection
    {
        return $this->vVTs;
    }

    public function addVVT(VVT $vVT): self
    {
        if (!$this->vVTs->contains($vVT)) {
            $this->vVTs[] = $vVT;
            $vVT->setUser($this);
        }

        return $this;
    }

    public function removeVVT(VVT $vVT): self
    {
        if ($this->vVTs->contains($vVT)) {
            $this->vVTs->removeElement($vVT);
            // set the owning side to null (unless already changed)
            if ($vVT->getUser() === $this) {
                $vVT->setUser(null);
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
            $auditTom->setUser($this);
        }

        return $this;
    }

    public function removeAuditTom(AuditTom $auditTom): self
    {
        if ($this->auditToms->contains($auditTom)) {
            $this->auditToms->removeElement($auditTom);
            // set the owning side to null (unless already changed)
            if ($auditTom->getUser() === $this) {
                $auditTom->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|VVTDsfa[]
     */
    public function getVVTDsfas(): Collection
    {
        return $this->vVTDsfas;
    }

    public function addVVTDsfa(VVTDsfa $vVTDsfa): self
    {
        if (!$this->vVTDsfas->contains($vVTDsfa)) {
            $this->vVTDsfas[] = $vVTDsfa;
            $vVTDsfa->setUser($this);
        }

        return $this;
    }

    public function removeVVTDsfa(VVTDsfa $vVTDsfa): self
    {
        if ($this->vVTDsfas->contains($vVTDsfa)) {
            $this->vVTDsfas->removeElement($vVTDsfa);
            // set the owning side to null (unless already changed)
            if ($vVTDsfa->getUser() === $this) {
                $vVTDsfa->setUser(null);
            }
        }

        return $this;
    }

    public function getAkademieUser(): ?Team
    {
        return $this->akademieUser;
    }

    public function setAkademieUser(?Team $akademieUser): self
    {
        $this->akademieUser = $akademieUser;

        return $this;
    }

    public function getAdminUser(): ?Team
    {
        return $this->adminUser;
    }

    public function setAdminUser(?Team $adminUser): self
    {
        $this->adminUser = $adminUser;

        return $this;
    }

    /**
     * @return Collection|VVT[]
     */
    public function getMyVvts(): Collection
    {
        return $this->myVvts;
    }

    public function addMyVvt(VVT $myVvt): self
    {
        if (!$this->myVvts->contains($myVvt)) {
            $this->myVvts[] = $myVvt;
            $myVvt->setUserContract($this);
        }

        return $this;
    }

    public function removeMyVvt(VVT $myVvt): self
    {
        if ($this->myVvts->contains($myVvt)) {
            $this->myVvts->removeElement($myVvt);
            // set the owning side to null (unless already changed)
            if ($myVvt->getUserContract() === $this) {
                $myVvt->setUserContract(null);
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
            $tom->setUser($this);
        }

        return $this;
    }

    public function removeTom(Tom $tom): self
    {
        if ($this->toms->contains($tom)) {
            $this->toms->removeElement($tom);
            // set the owning side to null (unless already changed)
            if ($tom->getUser() === $this) {
                $tom->setUser(null);
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
            $vorfall->setUser($this);
        }

        return $this;
    }

    public function removeVorfall(Vorfall $vorfall): self
    {
        if ($this->vorfalls->contains($vorfall)) {
            $this->vorfalls->removeElement($vorfall);
            // set the owning side to null (unless already changed)
            if ($vorfall->getUser() === $this) {
                $vorfall->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|AkademieKurse[]
     */
    public function getAkademieKurses(): Collection
    {
        return $this->akademieKurses;
    }

    public function addAkademieKurse(AkademieKurse $akademieKurse): self
    {
        if (!$this->akademieKurses->contains($akademieKurse)) {
            $this->akademieKurses[] = $akademieKurse;
            $akademieKurse->setUser($this);
        }

        return $this;
    }

    public function removeAkademieKurse(AkademieKurse $akademieKurse): self
    {
        if ($this->akademieKurses->contains($akademieKurse)) {
            $this->akademieKurses->removeElement($akademieKurse);
            // set the owning side to null (unless already changed)
            if ($akademieKurse->getUser() === $this) {
                $akademieKurse->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|VVT[]
     */
    public function getAssignedVvts(): Collection
    {
        return $this->assignedVvts;
    }

    public function addAssignedVvt(VVT $assignedVvt): self
    {
        if (!$this->assignedVvts->contains($assignedVvt)) {
            $this->assignedVvts[] = $assignedVvt;
            $assignedVvt->setAssignedUser($this);
        }

        return $this;
    }

    public function removeAssignedVvt(VVT $assignedVvt): self
    {
        if ($this->assignedVvts->contains($assignedVvt)) {
            $this->assignedVvts->removeElement($assignedVvt);
            // set the owning side to null (unless already changed)
            if ($assignedVvt->getAssignedUser() === $this) {
                $assignedVvt->setAssignedUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|AuditTom[]
     */
    public function getAssignedAudits(): Collection
    {
        return $this->assignedAudits;
    }

    public function addAssignedAudit(AuditTom $assignedAudit): self
    {
        if (!$this->assignedAudits->contains($assignedAudit)) {
            $this->assignedAudits[] = $assignedAudit;
            $assignedAudit->setAssignedUser($this);
        }

        return $this;
    }

    public function removeAssignedAudit(AuditTom $assignedAudit): self
    {
        if ($this->assignedAudits->contains($assignedAudit)) {
            $this->assignedAudits->removeElement($assignedAudit);
            // set the owning side to null (unless already changed)
            if ($assignedAudit->getAssignedUser() === $this) {
                $assignedAudit->setAssignedUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Datenweitergabe[]
     */
    public function getAssignedDatenweitergaben(): Collection
    {
        return $this->assignedDatenweitergaben;
    }

    public function addAssignedDatenweitergaben(Datenweitergabe $assignedDatenweitergaben): self
    {
        if (!$this->assignedDatenweitergaben->contains($assignedDatenweitergaben)) {
            $this->assignedDatenweitergaben[] = $assignedDatenweitergaben;
            $assignedDatenweitergaben->setAssignedUser($this);
        }

        return $this;
    }

    public function removeAssignedDatenweitergaben(Datenweitergabe $assignedDatenweitergaben): self
    {
        if ($this->assignedDatenweitergaben->contains($assignedDatenweitergaben)) {
            $this->assignedDatenweitergaben->removeElement($assignedDatenweitergaben);
            // set the owning side to null (unless already changed)
            if ($assignedDatenweitergaben->getAssignedUser() === $this) {
                $assignedDatenweitergaben->setAssignedUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|VVTDsfa[]
     */
    public function getAssignedDsfa(): Collection
    {
        return $this->assignedDsfa;
    }

    public function addAssignedDsfa(VVTDsfa $assignedDsfa): self
    {
        if (!$this->assignedDsfa->contains($assignedDsfa)) {
            $this->assignedDsfa[] = $assignedDsfa;
            $assignedDsfa->setAssignedUser($this);
        }

        return $this;
    }

    public function removeAssignedDsfa(VVTDsfa $assignedDsfa): self
    {
        if ($this->assignedDsfa->contains($assignedDsfa)) {
            $this->assignedDsfa->removeElement($assignedDsfa);
            // set the owning side to null (unless already changed)
            if ($assignedDsfa->getAssignedUser() === $this) {
                $assignedDsfa->setAssignedUser(null);
            }
        }

        return $this;
    }

}
