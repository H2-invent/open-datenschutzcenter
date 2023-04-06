<?php

namespace App\Entity;

use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;
use App\Repository\VVTDsfaRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VVTDsfaRepository::class)]
class VVTDsfa
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

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
    private $notwendigkeit;

    /**
     * @Assert\NotBlank()
     * @Encrypted()
     */
    #[ORM\Column(type: 'text')]
    private $risiko;

    /**
     * @Assert\NotBlank()
     * @Encrypted()
     */
    #[ORM\Column(type: 'text')]
    private $abhilfe;

    /**
     * @Assert\NotBlank()
     * @Encrypted()
     */
    #[ORM\Column(type: 'text')]
    private $standpunkt;

    /**
     * @Encrypted()
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $dsb;

    /**
     * @Encrypted()
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $ergebnis;

    #[ORM\Column(type: 'boolean')]
    private $activ;

    #[ORM\OneToOne(targetEntity: VVTDsfa::class, cascade: ['persist', 'remove'])]
    private $previous;

    #[ORM\Column(type: 'datetime')]
    private $CreatedAt;

    /**
     * @Assert\NotBlank()
     */
    #[ORM\ManyToOne(targetEntity: VVT::class, inversedBy: 'dsfa')]
    #[ORM\JoinColumn(nullable: false)]
    private $vvt;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'vVTDsfas')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'assignedDsfa')]
    private $assignedUser;


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

    public function getAssignedUser(): ?User
    {
        return $this->assignedUser;
    }

    public function setAssignedUser(?User $assignedUser): self
    {
        $this->assignedUser = $assignedUser;

        return $this;
    }
}
