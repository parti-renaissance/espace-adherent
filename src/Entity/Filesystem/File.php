<?php

namespace App\Entity\Filesystem;

use App\Entity\Administrator;
use App\Entity\EntityIdentityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Filesystem\FileRepository")
 * @ORM\Table(name="filesystem_file",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="filesystem_file_slug_unique", columns="slug")
 *     },
 *     indexes={
 *         @ORM\Index(columns={"type"}),
 *         @ORM\Index(columns={"name"})
 *     })
 *
 *     @UniqueEntity(fields={"name"}, repositoryMethod="findDirectoryByName", message="file.validation.name.not_unique")
 */
class File
{
    use EntityIdentityTrait;
    use TimestampableEntity;

    /**
     * @var string|null
     *
     * @ORM\Column(length=100)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=100)
     *
     * @SymfonySerializer\Groups({"autocomplete"})
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column
     * @Gedmo\Slug(fields={"name"}, unique=true)
     */
    private $slug;

    /**
     * @var string|null
     *
     * @ORM\Column(length=20)
     *
     * @Assert\Choice(
     *     callback={"App\Entity\Filesystem\FileTypeEnum", "toArray"},
     *     strict=true
     * )
     */
    private $type = FileTypeEnum::FILE;

    /**
     * @var UploadedFile|null
     *
     * @Assert\File(
     *     maxSize="5M",
     *     mimeTypes={
     *         "image/*",
     *         "video/mpeg",
     *         "video/mp4",
     *         "video/quicktime",
     *         "video/webm",
     *         "application/pdf",
     *         "application/x-pdf",
     *         "application/vnd.ms-powerpoint",
     *         "application/vnd.openxmlformats-officedocument.presentationml.presentation",
     *         "application/msword",
     *         "application/vnd.ms-excel",
     *         "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
     *         "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
     *         "application/rtf",
     *         "text/plain",
     *         "text/csv",
     *         "text/html",
     *         "text/calendar"
     *     }
     * )
     */
    private $file;

    /**
     * @var Administrator|null
     *
     * @Gedmo\Blameable(on="create")
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Administrator")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $createdBy;

    /**
     * @var Administrator|null
     *
     * @Gedmo\Blameable(on="update")
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Administrator")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $updatedBy;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private $displayed = true;

    /**
     * @var FilePermission[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Filesystem\FilePermission", mappedBy="file", cascade={"all"}, orphanRemoval=true)
     *
     * @Assert\Valid
     */
    private $permissions;

    /**
     * @var File|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Filesystem\File", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=true)
     *
     * @Assert\Valid
     */
    private $parent;

    /**
     * @var string|null
     *
     * @ORM\Column(length=255, nullable=true)
     *
     * @Assert\Length(max=255, maxMessage="file.validation.filename_length")
     */
    private $originalFilename;

    /**
     * @var string|null
     *
     * @ORM\Column(length=10, nullable=true)
     *
     * @Assert\Length(max=10)
     */
    private $extension;

    /**
     * @var string|null
     *
     * @ORM\Column(length=75, nullable=true)
     *
     * @Assert\Length(max=75)
     */
    private $mimeType;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", options={"unsigned": true}, nullable=true)
     */
    private $size;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true, nullable=true)
     *
     * @Assert\Url
     */
    private $externalLink;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->permissions = new ArrayCollection();
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(?UploadedFile $file): void
    {
        $this->file = $file;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getCreatedBy(): ?Administrator
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?Administrator $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    public function getUpdatedBy(): ?Administrator
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?Administrator $updatedBy): void
    {
        $this->updatedBy = $updatedBy;
    }

    public function isDisplayed(): bool
    {
        return $this->displayed;
    }

    public function setDisplayed(bool $displayed): void
    {
        $this->displayed = $displayed;
    }

    public function getParent(): ?File
    {
        return $this->parent;
    }

    public function setParent(?File $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return Collection|FilePermission[]
     */
    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

    public function addPermission(FilePermission $permission): void
    {
        if (!$this->permissions->contains($permission)) {
            $permission->setFile($this);
            $this->permissions->add($permission);
        }
    }

    public function removePermission(FilePermission $permission): void
    {
        $this->permissions->removeElement($permission);
        $permission->setFile(null);
    }

    public function getOriginalFilename(): ?string
    {
        return $this->originalFilename;
    }

    public function setOriginalFilename(?string $originalFilename): void
    {
        $this->originalFilename = $originalFilename;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setExtension(?string $extension): void
    {
        $this->extension = $extension;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): void
    {
        $this->mimeType = $mimeType;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): void
    {
        $this->size = $size;
    }

    public function getExternalLink(): ?string
    {
        return $this->externalLink;
    }

    public function setExternalLink(?string $externalLink): void
    {
        $this->externalLink = $externalLink;
    }

    public function getPath(): string
    {
        return sprintf('files/filesystem/%s', $this->getUuid()->toString());
    }

    public function getFullname(): ?string
    {
        switch ($this->type) {
            case FileTypeEnum::FILE:
                return \sprintf('%s.%s', $this->getName(), $this->getExtension());
            case FileTypeEnum::DIRECTORY:
                return \sprintf('/%s', $this->getName());
            case FileTypeEnum::EXTERNAL_LINK:
                return \sprintf('%s (un lien externe)', $this->getName());
            default:
                return null;
        }
    }

    public function getFullPath(): ?string
    {
        switch ($this->type) {
            case FileTypeEnum::FILE:
            case FileTypeEnum::EXTERNAL_LINK:
                return (string) $this->parent ? $this->parent->getFullname().'/'.$this->getFullname() : $this->getFullname();
            case FileTypeEnum::DIRECTORY:
                return $this->getFullname();
            default:
                return null;
        }
    }

    public function isFile(): bool
    {
        return FileTypeEnum::FILE === $this->type;
    }

    public function isDir(): bool
    {
        return FileTypeEnum::DIRECTORY === $this->type;
    }

    public function isLink(): bool
    {
        return FileTypeEnum::EXTERNAL_LINK === $this->type;
    }

    public function markAsFile(): void
    {
        $this->type = FileTypeEnum::FILE;
    }

    public function markAsDir(): void
    {
        $this->type = FileTypeEnum::DIRECTORY;
    }

    public function markAsLink(): void
    {
        $this->type = FileTypeEnum::EXTERNAL_LINK;
    }

    /**
     * @Assert\IsTrue(message="file.validation.file_or_link")
     */
    public function isValid(): bool
    {
        if ($this->isDir()) {
            return true;
        }

        return ($this->getId() && $this->size) || ($this->externalLink xor $this->file);
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }
}
