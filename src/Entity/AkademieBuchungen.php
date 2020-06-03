<?php

namespace App\Entity;

use App\Repository\AkademieBuchungenRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AkademieBuchungenRepository::class)
 */
class AkademieBuchungen
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
    private $zugewiesen;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $start;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $ende;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $abgeschlossen;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="buchungen")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=AkademieKurse::class, inversedBy="buchungen")
     * @ORM\JoinColumn(nullable=false)
     */
    private $kurs;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $vorlage;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $buchungsID;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $finishedID;

    /**
     * @ORM\Column(type="boolean")
     */
    private $invitation;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getZugewiesen(): ?\DateTimeInterface
    {
        return $this->zugewiesen;
    }

    public function setZugewiesen(\DateTimeInterface $zugewiesen): self
    {
        $this->zugewiesen = $zugewiesen;

        return $this;
    }

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(?\DateTimeInterface $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getEnde(): ?\DateTimeInterface
    {
        return $this->ende;
    }

    public function setEnde(?\DateTimeInterface $ende): self
    {
        $this->ende = $ende;

        return $this;
    }

    public function getAbgeschlossen(): ?bool
    {
        return $this->abgeschlossen;
    }

    public function setAbgeschlossen(?bool $abgeschlossen): self
    {
        $this->abgeschlossen = $abgeschlossen;

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

    public function getKurs(): ?AkademieKurse
    {
        return $this->kurs;
    }

    public function setKurs(?AkademieKurse $kurs): self
    {
        $this->kurs = $kurs;

        return $this;
    }

    public function getVorlage(): ?string
    {
        return $this->vorlage;
    }

    public function setVorlage(?string $vorlage): self
    {
        $this->vorlage = $vorlage;

        return $this;
    }

    public function getBuchungsID(): ?string
    {
        return $this->buchungsID;
    }

    public function setBuchungsID(?string $buchungsID): self
    {
        $this->buchungsID = $buchungsID;

        return $this;
    }

    public function getFinishedID(): ?string
    {
        return $this->finishedID;
    }

    public function setFinishedID(?string $finishedID): self
    {
        $this->finishedID = $finishedID;

        return $this;
    }

    public function getInvitation(): ?bool
    {
        return $this->invitation;
    }

    public function setInvitation(bool $invitation): self
    {
        $this->invitation = $invitation;

        return $this;
    }
}
