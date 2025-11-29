<?php

declare(strict_types=1);

namespace App\Entity\Filesystem;

use App\Entity\Administrator;
use App\Entity\EntityIdentityTrait;
use App\Repository\Filesystem\FileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Timestampable;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FileRepository::class)]
#[ORM\Index(columns: ['type'])]
#[ORM\Index(columns: ['name'])]
#[ORM\Table(name: 'filesystem_file')]
#[UniqueEntity(fields: ['name'], message: 'file.validation.name.not_unique', repositoryMethod: 'findDirectoryByName')]
class File implements Timestampable
{
    use EntityIdentityTrait;
    use TimestampableEntity;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 100)]
    #[Assert\NotBlank]
    #[Groups(['autocomplete'])]
    #[ORM\Column(length: 100)]
    private $name;

    /**
     * @var string|null
     */
    #[Gedmo\Slug(fields: ['name'], unique: true)]
    #[ORM\Column(unique: true)]
    private $slug;

    /**
     * @var string|null
     */
    #[Assert\Choice(callback: [FileTypeEnum::class, 'toArray'])]
    #[ORM\Column(length: 20)]
    private $type = FileTypeEnum::FILE;

    /**
     * @var UploadedFile|null
     */
    #[Assert\File(maxSize: '5M', mimeTypes: ['image/*', 'video/mpeg', 'video/mp4', 'video/quicktime', 'video/webm', 'application/pdf', 'application/x-pdf', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/msword', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/rtf', 'text/plain', 'text/csv', 'text/html', 'text/calendar'])]
    private $file;

    /**
     * @var Administrator|null
     */
    #[Gedmo\Blameable(on: 'create')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Administrator::class)]
    private $createdBy;

    /**
     * @var Administrator|null
     */
    #[Gedmo\Blameable(on: 'update')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Administrator::class)]
    private $updatedBy;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private $displayed = true;

    /**
     * @var FilePermission[]|Collection
     */
    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'file', targetEntity: FilePermission::class, cascade: ['all'], orphanRemoval: true)]
    private $permissions;

    /**
     * @var File|null
     */
    #[Assert\Valid]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: File::class, cascade: ['persist'], inversedBy: 'children')]
    private $parent;

    /**
     * @var File[]|Collection
     */
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: File::class, fetch: 'EXTRA_LAZY')]
    private $children;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 255, maxMessage: 'file.validation.filename_length')]
    #[ORM\Column(nullable: true)]
    private $originalFilename;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 10)]
    #[ORM\Column(length: 10, nullable: true)]
    private $extension;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 75)]
    #[ORM\Column(length: 75, nullable: true)]
    private $mimeType;

    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer', nullable: true, options: ['unsigned' => true])]
    private $size;

    /**
     * @var string|null
     */
    #[Assert\Url]
    #[ORM\Column(nullable: true)]
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

    public function getChildren(): Collection
    {
        return $this->children;
    }

    /**
     * @return Collection|FilePermission[]
     */
    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

    public function getPermissionNames(): array
    {
        return array_map(function (FilePermission $permission) {
            return $permission->getName();
        }, $this->permissions->toArray());
    }

    public function hasPermission(string $name): bool
    {
        return $this->getPermissions()->filter(function (FilePermission $permission) use ($name) {
            return $permission->getName() === $name;
        })->count() > 0;
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
        return \sprintf('files/filesystem/%s', $this->getUuid()->toString());
    }

    public function getNameWithExtension(): ?string
    {
        return $this->isFile()
            ? \sprintf('%s.%s', $this->getName(), $this->getExtension())
            : '';
    }

    public function getFullname(): ?string
    {
        return match ($this->type) {
            FileTypeEnum::FILE => \sprintf('%s.%s', $this->getName(), $this->getExtension()),
            FileTypeEnum::DIRECTORY => \sprintf('/%s', $this->getName()),
            FileTypeEnum::EXTERNAL_LINK => \sprintf('%s (un lien externe)', $this->getName()),
            default => null,
        };
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

    public function isPdf(): bool
    {
        return 'application/x-pdf' === $this->mimeType || 'application/pdf' === $this->mimeType;
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

    #[Assert\IsTrue(message: 'file.validation.file_or_link')]
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
