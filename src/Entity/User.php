<?php
// src/Entity/User.php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Table(name: 'user')]
#[ORM\Entity]
class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected $id;

    #[ORM\ManyToMany(targetEntity: Team::class, inversedBy: 'members')]
    private Collection $teams;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Datenweitergabe::class)]
    private Collection $datenweitergabes;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: VVT::class)]
    private Collection $vVTs;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: AuditTom::class)]
    private Collection $auditToms;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: VVTDsfa::class)]
    private Collection $vVTDsfas;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'akademieUsers')]
    private ?Team $akademieUser = null;

    #[ORM\ManyToMany(targetEntity: Team::class, mappedBy: 'admins')]
    private $adminRoles;

    #[ORM\OneToMany(mappedBy: 'userContract', targetEntity: VVT::class)]
    private Collection $myVvts;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Tom::class)]
    private Collection $toms;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Vorfall::class)]
    private Collection $vorfalls;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: AkademieKurse::class)]
    private Collection $akademieKurses;

    #[ORM\OneToMany(mappedBy: 'assignedUser', targetEntity: VVT::class)]
    private Collection $assignedVvts;

    #[ORM\OneToMany(mappedBy: 'assignedUser', targetEntity: AuditTom::class)]
    private Collection $assignedAudits;

    #[ORM\OneToMany(mappedBy: 'assignedUser', targetEntity: Datenweitergabe::class)]
    private Collection $assignedDatenweitergaben;

    #[ORM\OneToMany(mappedBy: 'assignedUser', targetEntity: VVTDsfa::class)]
    private Collection $assignedDsfa;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Forms::class)]
    private Collection $forms;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Policies::class)]
    private Collection $policies;

    #[ORM\OneToMany(mappedBy: 'person', targetEntity: Policies::class)]
    private Collection $policiesResponsible;

    #[ORM\OneToMany(mappedBy: 'assignedUser', targetEntity: Policies::class)]
    private Collection $assignedPolicies;

    #[ORM\OneToMany(mappedBy: 'assignedUser', targetEntity: Forms::class)]
    private Collection $assignedForms;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Software::class)]
    private Collection $software;

    #[ORM\OneToMany(mappedBy: 'assignedUser', targetEntity: Software::class)]
    private Collection $assignedSoftware;

    #[ORM\OneToMany(mappedBy: 'assignedUser', targetEntity: Vorfall::class)]
    private Collection $assignedVorfalls;

    #[ORM\OneToMany(mappedBy: 'assignedUser', targetEntity: Task::class)]
    private Collection $tasks;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ClientRequest::class)]
    private Collection $clientRequests;

    #[ORM\OneToMany(mappedBy: 'assignedUser', targetEntity: ClientRequest::class)]
    private Collection $assignedRequests;

    #[ORM\OneToMany(mappedBy: 'dsbUser', targetEntity: Team::class)]
    private Collection $teamDsb;

    #[ORM\Column(type: 'text')]
    private ?string $email;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $keycloakId;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $createdAt;

    #[ORM\Column(type: 'text', nullable: true)]
    private string $username;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $lastLogin;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $firstName;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $lastName;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $registerId;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Loeschkonzept::class)]
    private Collection $loeschkonzepts;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: VVTDatenkategorie::class)]
    private Collection $vVTDatenkategories;

    #[ORM\Column(type: 'json', nullable: true)]
    private array $roles;

    public function __construct()
    {
        $this->teams = new ArrayCollection();
        $this->datenweitergabes = new ArrayCollection();
        $this->vVTs = new ArrayCollection();
        $this->auditToms = new ArrayCollection();
        $this->vVTDsfas = new ArrayCollection();
        $this->adminRoles = new ArrayCollection();
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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function hasRole(string $role): bool
    {
        foreach ($this->getRoles() as $_role) {
            if ($role === $_role) {
                return true;
            }
        }

        return false;
    }

    public function getTeams(): Collection
    {
        $allTeams = array_merge($this->teams->toArray(), $this->adminRoles->toArray());
        return new ArrayCollection(array_unique($allTeams));
    }

    public function setTeams(Collection $teams): self
    {
        $this->teams = $teams;

        return $this;
    }

    public function addTeam(Team $team): self
    {
        if(!$this->teams->contains($team)) {
            $this->teams[] = $team;
        }

        return $this;
    }

    public function removeTeam(Team $team): self
    {
        if ($this->teams->contains($team)) {
            $this->teams->removeElement($team);
        }

        return $this;
    }

    public function hasTeam(Team $team = null): bool
    {
        if ($team) {
            return $this->getTeams()->contains($team);
        }
        return count($this->getTeams()) > 0;
    }

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

    public function  getAkademieUser(): ?Team
    {
        return $this->akademieUser;
    }

    public function setAkademieUser(?Team $akademieUser): self
    {
        $this->akademieUser = $akademieUser;

        return $this;
    }

    public function getAdminRoles(): Collection
    {
        return $this->adminRoles;
    }

    public function hasAdminRole(Team $team = null): bool
    {
        if ($team && $this->adminRoles) {
            return $this->adminRoles->contains($team);
        }
        return count($this->adminRoles) > 0;
    }

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

    public function getUsername(): string
    {
        return $this->username;
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

    public function getRoles(): array
    {
        $roles = $this->roles ?? [];
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUserIdentifier(): string
    {
        return $this->getUsername();
    }
}
