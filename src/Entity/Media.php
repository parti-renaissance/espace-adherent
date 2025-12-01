<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\MediaRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MediaRepository::class)]
#[ORM\Table(name: 'medias')]
#[UniqueEntity(fields: ['path'])]
class Media implements \Stringable
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'bigint')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[ORM\Column]
    private $name;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[ORM\Column(unique: true)]
    private $path;

    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer')]
    private $width;

    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer')]
    private $height;

    /**
     * @var int|null
     */
    #[ORM\Column(type: 'bigint')]
    private $size;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 50)]
    private $mimeType;

    /**
     * @var \DateTime
     */
    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    /**
     * @var \DateTime
     */
    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: 'datetime')]
    private $updatedAt;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private $compressedDisplay = true;

    /**
     * @var UploadedFile|null
     */
    #[Assert\File(maxSize: '5M', binaryFormat: false, mimeTypes: ['image/jpeg', 'image/gif', 'image/png', 'video/mpeg', 'video/mp4', 'video/quicktime', 'video/webm'])]
    private $file;

    public function __toString()
    {
        return $this->name ?: '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string|null $path
     */
    public function setPath($path): self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param int|null $width
     */
    public function setWidth($width): self
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param int|null $height
     */
    public function setHeight($height): self
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param int|null $size
     */
    public function setSize($size): self
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @param string|null $mimeType
     */
    public function setMimeType($mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function isCompressedDisplay(): bool
    {
        return $this->compressedDisplay;
    }

    public function setCompressedDisplay(bool $compressedDisplay): void
    {
        $this->compressedDisplay = $compressedDisplay;
    }

    /**
     * @return UploadedFile|null
     */
    public function getFile()
    {
        return $this->file;
    }

    public function setFile(?File $file = null): self
    {
        if (!$file || !$file->getPathname()) {
            return $this;
        }

        $this->width = 0;
        $this->height = 0;
        $this->mimeType = $file->getMimeType();
        $this->size = $file->getSize();
        $this->file = $file;

        if ($infos = getimagesize($file->getPathname())) {
            $this->width = $infos[0];
            $this->height = $infos[1];
            $this->mimeType = $infos['mime'];
        }

        return $this;
    }

    public function isVideo(): bool
    {
        return str_contains($this->mimeType, 'video');
    }

    public function getPathWithDirectory(): string
    {
        return \sprintf('%s/%s', $this->isVideo() ? 'videos' : 'images', $this->getPath());
    }
}
