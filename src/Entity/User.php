<?php
// src/Entity/User.php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\UserBase as BaseUser;
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

    /**
     * @ORM\OneToMany(targetEntity=Forms::class, mappedBy="user")
     */
    private $forms;

    /**
     * @ORM\OneToMany(targetEntity=Policies::class, mappedBy="user")
     */
    private $policies;

    /**
     * @ORM\OneToMany(targetEntity=Policies::class, mappedBy="person")
     */
    private $policiesResponsible;

    /**
     * @ORM\OneToMany(targetEntity=Policies::class, mappedBy="assignedUser")
     */
    private $assignedPolicies;

    /**
     * @ORM\OneToMany(targetEntity=Forms::class, mappedBy="assignedUser")
     */
    private $assignedForms;

    /**
     * @ORM\OneToMany(targetEntity=Software::class, mappedBy="user")
     */
    private $software;

    /**
     * @ORM\OneToMany(targetEntity=Software::class, mappedBy="assignedUser")
     */
    private $assignedSoftware;

    /**
     * @ORM\OneToMany(targetEntity=Vorfall::class, mappedBy="assignedUser")
     */
    private $assignedVorfalls;

    /**
     * @ORM\OneToMany(targetEntity=Task::class, mappedBy="assignedUser")
     */
    private $tasks;

    /**
     * @ORM\OneToMany(targetEntity=ClientRequest::class, mappedBy="user")
     */
    private $clientRequests;

    /**
     * @ORM\OneToMany(targetEntity=ClientRequest::class, mappedBy="assignedUser")
     */
    private $assignedRequests;

    /**
     * @ORM\OneToMany(targetEntity=Team::class, mappedBy="dsbUser")
     */
    private $teamDsb;

    /**
     * @ORM\Column(type="text")
     */
    private $email;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $keycloakId;

    /**
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $username;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastLogin;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $firstName;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $lastName;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $registerId;

    /**
     * @ORM\OneToMany(targetEntity=Loeschkonzept::class, mappedBy="user")
     */
    private $loeschkonzepts;

    /**
     * @ORM\OneToMany(targetEntity=VVTDatenkategorie::class, mappedBy="user")
     */
    private $vVTDatenkategories;


    public function __construct()
    {
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
        $this->forms = new ArrayCollection();
        $this->policies = new ArrayCollection();
        $this->policiesResponsible = new ArrayCollection();
        $this->assignedPolicies = new ArrayCollection();
        $this->assignedForms = new ArrayCollection();
        $this->software = new ArrayCollection();
        $this->assignedSoftware = new ArrayCollection();
        $this->assignedVorfalls = new ArrayCollection();
        $this->tasks = new ArrayCollection();
        $this->clientRequests = new ArrayCollection();
        $this->assignedRequests = new ArrayCollection();
        $this->teamDsb = new ArrayCollection();
        $this->loeschkonzepts = new ArrayCollection();
        $this->vVTDatenkategories = new ArrayCollection();
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
            $form->setUser($this);
        }

        return $this;
    }

    public function removeForm(Forms $form): self
    {
        if ($this->forms->contains($form)) {
            $this->forms->removeElement($form);
            // set the owning side to null (unless already changed)
            if ($form->getUser() === $this) {
                $form->setUser(null);
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
            $policy->setUser($this);
        }

        return $this;
    }

    public function removePolicy(Policies $policy): self
    {
        if ($this->policies->contains($policy)) {
            $this->policies->removeElement($policy);
            // set the owning side to null (unless already changed)
            if ($policy->getUser() === $this) {
                $policy->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Policies[]
     */
    public function getPoliciesResponsible(): Collection
    {
        return $this->policiesResponsible;
    }

    public function addPoliciesResponsible(Policies $policiesResponsible): self
    {
        if (!$this->policiesResponsible->contains($policiesResponsible)) {
            $this->policiesResponsible[] = $policiesResponsible;
            $policiesResponsible->setPerson($this);
        }

        return $this;
    }

    public function removePoliciesResponsible(Policies $policiesResponsible): self
    {
        if ($this->policiesResponsible->contains($policiesResponsible)) {
            $this->policiesResponsible->removeElement($policiesResponsible);
            // set the owning side to null (unless already changed)
            if ($policiesResponsible->getPerson() === $this) {
                $policiesResponsible->setPerson(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Policies[]
     */
    public function getAssignedPolicies(): Collection
    {
        return $this->assignedPolicies;
    }

    public function addAssignedPolicy(Policies $assignedPolicy): self
    {
        if (!$this->assignedPolicies->contains($assignedPolicy)) {
            $this->assignedPolicies[] = $assignedPolicy;
            $assignedPolicy->setAssignedUser($this);
        }

        return $this;
    }

    public function removeAssignedPolicy(Policies $assignedPolicy): self
    {
        if ($this->assignedPolicies->contains($assignedPolicy)) {
            $this->assignedPolicies->removeElement($assignedPolicy);
            // set the owning side to null (unless already changed)
            if ($assignedPolicy->getAssignedUser() === $this) {
                $assignedPolicy->setAssignedUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Forms[]
     */
    public function getAssignedForms(): Collection
    {
        return $this->assignedForms;
    }

    public function addAssignedForm(Forms $assignedForm): self
    {
        if (!$this->assignedForms->contains($assignedForm)) {
            $this->assignedForms[] = $assignedForm;
            $assignedForm->setAssignedUser($this);
        }

        return $this;
    }

    public function removeAssignedForm(Forms $assignedForm): self
    {
        if ($this->assignedForms->contains($assignedForm)) {
            $this->assignedForms->removeElement($assignedForm);
            // set the owning side to null (unless already changed)
            if ($assignedForm->getAssignedUser() === $this) {
                $assignedForm->setAssignedUser(null);
            }
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
            $software->setUser($this);
        }

        return $this;
    }

    public function removeSoftware(Software $software): self
    {
        if ($this->software->contains($software)) {
            $this->software->removeElement($software);
            // set the owning side to null (unless already changed)
            if ($software->getUser() === $this) {
                $software->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Software[]
     */
    public function getAssignedSoftware(): Collection
    {
        return $this->assignedSoftware;
    }

    public function addAssignedSoftware(Software $assignedSoftware): self
    {
        if (!$this->assignedSoftware->contains($assignedSoftware)) {
            $this->assignedSoftware[] = $assignedSoftware;
            $assignedSoftware->setAssignedUser($this);
        }

        return $this;
    }

    public function removeAssignedSoftware(Software $assignedSoftware): self
    {
        if ($this->assignedSoftware->contains($assignedSoftware)) {
            $this->assignedSoftware->removeElement($assignedSoftware);
            // set the owning side to null (unless already changed)
            if ($assignedSoftware->getAssignedUser() === $this) {
                $assignedSoftware->setAssignedUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Vorfall[]
     */
    public function getAssignedVorfalls(): Collection
    {
        return $this->assignedVorfalls;
    }

    public function addAssignedVorfall(Vorfall $assignedVorfall): self
    {
        if (!$this->assignedVorfalls->contains($assignedVorfall)) {
            $this->assignedVorfalls[] = $assignedVorfall;
            $assignedVorfall->setAssignedUser($this);
        }

        return $this;
    }

    public function removeAssignedVorfall(Vorfall $assignedVorfall): self
    {
        if ($this->assignedVorfalls->contains($assignedVorfall)) {
            $this->assignedVorfalls->removeElement($assignedVorfall);
            // set the owning side to null (unless already changed)
            if ($assignedVorfall->getAssignedUser() === $this) {
                $assignedVorfall->setAssignedUser(null);
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
            $task->setAssignedUser($this);
        }

        return $this;
    }

    public function removeTask(Task $task): self
    {
        if ($this->tasks->contains($task)) {
            $this->tasks->removeElement($task);
            // set the owning side to null (unless already changed)
            if ($task->getAssignedUser() === $this) {
                $task->setAssignedUser(null);
            }
        }

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
            $clientRequest->setUser($this);
        }

        return $this;
    }

    public function removeClientRequest(ClientRequest $clientRequest): self
    {
        if ($this->clientRequests->contains($clientRequest)) {
            $this->clientRequests->removeElement($clientRequest);
            // set the owning side to null (unless already changed)
            if ($clientRequest->getUser() === $this) {
                $clientRequest->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ClientRequest[]
     */
    public function getAssignedRequests(): Collection
    {
        return $this->assignedRequests;
    }

    public function addAssignedRequest(ClientRequest $assignedRequest): self
    {
        if (!$this->assignedRequests->contains($assignedRequest)) {
            $this->assignedRequests[] = $assignedRequest;
            $assignedRequest->setAssignedUser($this);
        }

        return $this;
    }

    public function removeAssignedRequest(ClientRequest $assignedRequest): self
    {
        if ($this->assignedRequests->contains($assignedRequest)) {
            $this->assignedRequests->removeElement($assignedRequest);
            // set the owning side to null (unless already changed)
            if ($assignedRequest->getAssignedUser() === $this) {
                $assignedRequest->setAssignedUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Team[]
     */
    public function getTeamDsb(): Collection
    {
        return $this->teamDsb;
    }

    public function addTeamDsb(Team $teamDsb): self
    {
        if (!$this->teamDsb->contains($teamDsb)) {
            $this->teamDsb[] = $teamDsb;
            $teamDsb->setDsbUser($this);
        }

        return $this;
    }

    public function removeTeamDsb(Team $teamDsb): self
    {
        if ($this->teamDsb->contains($teamDsb)) {
            $this->teamDsb->removeElement($teamDsb);
            // set the owning side to null (unless already changed)
            if ($teamDsb->getDsbUser() === $this) {
                $teamDsb->setDsbUser(null);
            }
        }

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

    public function getKeycloakId(): ?string
    {
        return $this->keycloakId;
    }

    public function setKeycloakId(?string $keycloakId): self
    {
        $this->keycloakId = $keycloakId;

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

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?\DateTimeInterface $lastLogin): self
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getRegisterId(): ?string
    {
        return $this->registerId;
    }

    public function setRegisterId(?string $registerId): self
    {
        $this->registerId = $registerId;

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
            $loeschkonzept->setUser($this);
        }

        return $this;
    }

    public function removeLoeschkonzept(Loeschkonzept $loeschkonzept): self
    {
        if ($this->loeschkonzepts->removeElement($loeschkonzept)) {
            // set the owning side to null (unless already changed)
            if ($loeschkonzept->getUser() === $this) {
                $loeschkonzept->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, VVTDatenkategorie>
     */
    public function getVVTDatenkategories(): Collection
    {
        return $this->vVTDatenkategories;
    }

    public function addVVTDatenkategory(VVTDatenkategorie $vVTDatenkategory): self
    {
        if (!$this->vVTDatenkategories->contains($vVTDatenkategory)) {
            $this->vVTDatenkategories[] = $vVTDatenkategory;
            $vVTDatenkategory->setUser($this);
        }

        return $this;
    }

    public function removeVVTDatenkategory(VVTDatenkategorie $vVTDatenkategory): self
    {
        if ($this->vVTDatenkategories->removeElement($vVTDatenkategory)) {
            // set the owning side to null (unless already changed)
            if ($vVTDatenkategory->getUser() === $this) {
                $vVTDatenkategory->setUser(null);
            }
        }

        return $this;
    }

}
