<?php

namespace App\Entity;

use App\Repository\AkademieKurseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=AkademieKurseRepository::class)
 */
class AkademieKurse
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $video;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $beschreibung;

    /**
     * @ORM\ManyToMany(targetEntity=Team::class, inversedBy="kurse")
     */
    private $team;

    /**
     * @ORM\OneToMany(targetEntity=AkademieBuchungen::class, mappedBy="kurs")
     */
    private $buchungen;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $activ;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="akademieKurses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="integer")
     */
    private $type;

    public function __construct()
    {
        $this->team = new ArrayCollection();
        $this->buchungen = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getVideo(): ?string
    {
        return $this->video;
    }

    public function setVideo(string $video): self
    {
        $this->video = $video;

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

    public function getBeschreibung(): ?string
    {
        return $this->beschreibung;
    }

    public function setBeschreibung(string $beschreibung): self
    {
        $this->beschreibung = $beschreibung;

        return $this;
    }

    /**
     * @return Collection|Team[]
     */
    public function getTeam(): Collection
    {
        return $this->team;
    }

    public function addTeam(Team $team): self
    {
        if (!$this->team->contains($team)) {
            $this->team[] = $team;
        }

        return $this;
    }

    public function removeTeam(Team $team): self
    {
        if ($this->team->contains($team)) {
            $this->team->removeElement($team);
        }

        return $this;
    }

    /**
     * @return Collection|AkademieBuchungen[]
     */
    public function getBuchungen(): Collection
    {
        return $this->buchungen;
    }

    public function addBuchungen(AkademieBuchungen $buchungen): self
    {
        if (!$this->buchungen->contains($buchungen)) {
            $this->buchungen[] = $buchungen;
            $buchungen->setKurs($this);
        }

        return $this;
    }

    public function removeBuchungen(AkademieBuchungen $buchungen): self
    {
        if ($this->buchungen->contains($buchungen)) {
            $this->buchungen->removeElement($buchungen);
            // set the owning side to null (unless already changed)
            if ($buchungen->getKurs() === $this) {
                $buchungen->setKurs(null);
            }
        }

        return $this;
    }

    public function getActiv(): ?bool
    {
        return $this->activ;
    }

    public function setActiv(?bool $activ): self
    {
        $this->activ = $activ;

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

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }
}
