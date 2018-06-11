<?php

namespace AppBundle\Entity\Mooc;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\EntityFileInterface;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="mooc_attachment_file",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="mooc_attachment_file_slug_extension", columns={"slug", "extension"})}
 * )
 * @ORM\EntityListeners({"AppBundle\EntityListener\FileListener"})
 *
 * @Algolia\Index(autoIndex=false)
 */
class AttachmentFile implements EntityFileInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     */
    private $title;

    /**
     * @ORM\Column
     * @Gedmo\Slug(fields={"title"})
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $path;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $extension;

    /**
     * @var UploadedFile|null
     *
     * @Assert\File(maxSize="5M")
     */
    private $file;

    /**
     * @var string|null
     */
    private $type;

    public function __construct()
    {
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

    public function getPrefixPath(): string
    {
        if ($this->type) {
            return 'files/mooc/'.$this->type;
        }

        return 'files/mooc';
    }

    public function setExtension(?string $extension): void
    {
        $this->extension = $extension;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }
}
