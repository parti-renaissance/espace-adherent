<?php

namespace App\Entity;

use App\EntityListener\FileListener;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\MappedSuperclass]
#[ORM\EntityListeners([FileListener::class])]
abstract class BaseFile implements EntityFileInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    private $id;

    /**
     * @var string
     */
    #[Groups(['formation_read', 'formation_list_read', 'formation_write'])]
    #[ORM\Column]
    #[Assert\NotBlank]
    private $title;

    #[Groups(['formation_read', 'formation_list_read', 'formation_write'])]
    #[ORM\Column]
    #[Gedmo\Slug(fields: ['title'])]
    private $slug;

    /**
     * @var string
     */
    #[Groups(['formation_read', 'formation_list_read'])]
    #[ORM\Column]
    private $path;

    /**
     * @var string
     */
    #[Groups(['formation_read', 'formation_list_read'])]
    #[ORM\Column]
    private $extension;

    /**
     * @var UploadedFile|null
     */
    #[Assert\File(maxSize: '5M')]
    protected $file;

    public function __construct(
        ?string $title = null,
        ?string $slug = null,
        ?string $extension = null,
        ?string $path = null
    ) {
        $this->title = $title;
        $this->slug = $slug;
        $this->extension = $extension;
        $this->path = $path;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): void
    {
        $this->path = $path;
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(?UploadedFile $file): void
    {
        $this->file = $file;
    }

    public function setExtension(?string $extension): void
    {
        $this->extension = $extension;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    abstract public function getPrefixPath(): string;
}
