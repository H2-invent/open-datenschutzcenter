<?php

namespace App\Entity;

use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;
use App\Repository\ClientCommentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClientCommentRepository::class)]
class ClientComment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    /**
     * @Encrypted()
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $name;

    /**
     * @Encrypted()
     */
    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private $comment;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    #[ORM\Column(type: 'boolean')]
    private $internal;

    #[ORM\ManyToOne(targetEntity: ClientRequest::class, inversedBy: 'clientComments')]
    #[ORM\JoinColumn(nullable: false)]
    private $clientRequest;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

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

    public function getInternal(): ?bool
    {
        return $this->internal;
    }

    public function setInternal(bool $internal): self
    {
        $this->internal = $internal;

        return $this;
    }

    public function getClientRequest(): ?ClientRequest
    {
        return $this->clientRequest;
    }

    public function setClientRequest(?ClientRequest $clientRequest): self
    {
        $this->clientRequest = $clientRequest;

        return $this;
    }
}
