<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\File as SfFile;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity]
#[Vich\Uploadable]
class UploadableFile
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    #[ORM\Embedded(class: EmbeddedFile::class)]
    private EmbeddedFile $file;

    #[Assert\File(maxSize: '5M', binaryFormat: false)]
    #[Vich\UploadableField(mapping: 'uploadable_file', fileNameProperty: 'file.name', size: 'file.size', mimeType: 'file.mimeType', originalName: 'file.originalName', dimensions: 'file.dimensions')]
    public ?SfFile $uploadFile = null;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->file = new EmbeddedFile();
    }

    public function setUploadFile(?SfFile $file = null): void
    {
        $this->uploadFile = $file;

        if (null !== $file) {
            $this->updatedAt = new \DateTime();
        }
    }

    public function getUploadFile(): ?SfFile
    {
        return $this->uploadFile;
    }

    public function setFile(EmbeddedFile $file): void
    {
        $this->file = $file;
    }

    public function getFile(): ?EmbeddedFile
    {
        return $this->file;
    }

    public function getName(): ?string
    {
        return $this->file->getName();
    }

    public function getOriginalName(): ?string
    {
        return $this->file->getOriginalName();
    }

    public function getMimeType(): ?string
    {
        return $this->file->getMimeType();
    }

    public function getSize(): ?int
    {
        return $this->file->getSize();
    }

    public function getWidth(): ?int
    {
        return $this->file->getWidth();
    }

    public function getHeight(): ?int
    {
        return $this->file->getHeight();
    }

    public function getPath(): ?string
    {
        $name = $this->file->getName();

        return null === $name ? null : substr($name, 0, 2).'/'.$name;
    }
}
