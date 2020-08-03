<?php

namespace App\Entity;

use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;
use App\Repository\SoftwareConfigRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass=SoftwareConfigRepository::class)
 * @Vich\Uploadable
 */
class SoftwareConfig
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Encrypted()
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     * @Encrypted()
     * @Assert\NotBlank()
     */
    private $config;

    /**
     * @ORM\Column(type="boolean")
     */
    private $activ;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=Software::class, inversedBy="config")
     * @ORM\JoinColumn(nullable=false)
     */
    private $software;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     * @var string
     */
    private $upload;

    /**
     * @Assert\File(
     *     maxSize = "600k",
     *     mimeTypes = {"image/jpg", "image/jpeg", "image/gif", "image/png"},
     *     mimeTypesMessage = "Please upload a valid JP, JPEG, GIF or PNG"
     * )
     *
     * @Vich\UploadableField(mapping="software", fileNameProperty="upload")
     * @var File
     */
    private $uploadFile;


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

    public function getConfig(): ?string
    {
        return $this->config;
    }

    public function setConfig(string $config): self
    {
        $this->config = $config;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getSoftware(): ?Software
    {
        return $this->software;
    }

    public function setSoftware(?Software $software): self
    {
        $this->software = $software;

        return $this;
    }

    public function setUploadFile(File $upload = null)
    {
        $this->uploadFile = $upload;

        // VERY IMPORTANT:
        // It is required that at least one field changes if you are using Doctrine,
        // otherwise the event listeners won't be called and the file is lost
        if ($upload) {
            // if 'updatedAt' is not defined in your entity, use another property
            $this->createdAt = new \DateTime('now');
        }
    }

    public function getUploadFile()
    {
        return $this->uploadFile;
    }

    public function setUpload($upload)
    {
        $this->upload = $upload;
    }

    public function getUpload()
    {
        return $this->upload;
    }
}
