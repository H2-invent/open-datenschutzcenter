<?php

namespace App\Entity;

use App\Repository\VVTDsfaRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;

/**
 * @ORM\Entity(repositoryClass=VVTDsfaRepository::class)
 */
class VVTDsfa
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
     * @Encrypted()
     */
    private $beschreibung;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Encrypted()
     */
    private $notwendigkeit;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Encrypted()
     */
    private $risiko;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Encrypted()
     */
    private $abhilfe;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Encrypted()
     */
    private $standpunkt;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Encrypted()
     */
    private $dsb;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Encrypted()
     */
    private $ergebnis;

    /**
     * @ORM\Column(type="boolean")
     */
    private $activ;

    /**
     * @ORM\OneToOne(targetEntity=VVTDsfa::class, cascade={"persist", "remove"})
     */
    private $previous;

    /**
     * @ORM\Column(type="datetime")
     */
    private $CreatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=VVT::class, inversedBy="dsfa")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank()
     */
    private $vvt;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="vVTDsfas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;


    public function getId(): ?int
    {
        return $this->id;
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

    public function getNotwendigkeit(): ?string
    {
        return $this->notwendigkeit;
    }

    public function setNotwendigkeit(string $notwendigkeit): self
    {
        $this->notwendigkeit = $notwendigkeit;

        return $this;
    }

    public function getRisiko(): ?string
    {
        return $this->risiko;
    }

    public function setRisiko(string $risiko): self
    {
        $this->risiko = $risiko;

        return $this;
    }

    public function getAbhilfe(): ?string
    {
        return $this->abhilfe;
    }

    public function setAbhilfe(string $abhilfe): self
    {
        $this->abhilfe = $abhilfe;

        return $this;
    }

    public function getStandpunkt(): ?string
    {
        return $this->standpunkt;
    }

    public function setStandpunkt(string $standpunkt): self
    {
        $this->standpunkt = $standpunkt;

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

    public function getErgebnis(): ?string
    {
        return $this->ergebnis;
    }

    public function setErgebnis(?string $ergebnis): self
    {
        $this->ergebnis = $ergebnis;

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
        return $this->CreatedAt;
    }

    public function setCreatedAt(\DateTimeInterface $CreatedAt): self
    {
        $this->CreatedAt = $CreatedAt;

        return $this;
    }

    public function getVvt(): ?VVT
    {
        return $this->vvt;
    }

    public function setVvt(?VVT $vvt): self
    {
        $this->vvt = $vvt;

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
}
