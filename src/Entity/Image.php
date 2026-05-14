<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Timestampable;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Image implements Timestampable
{
    use TimestampableEntity;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    /**
     * @var Uuid
     */
    #[ORM\Column(type: 'uuid', unique: true)]
    protected $uuid;

    /**
     * @var string
     */
    #[Assert\Length(max: 10)]
    #[Assert\NotBlank]
    #[ORM\Column(length: 10)]
    private $extension;

    /**
     * @var File|null
     */
    #[Assert\File(maxSize: '10M', binaryFormat: false, mimeTypes: ['image/*'])]
    private $file;

    private $deleted = false;

    public function __construct(?Uuid $uuid = null)
    {
        $this->uuid = $uuid ?: Uuid::v4();

        $now = new \DateTime();
        $this
            ->setCreatedAt($now)
            ->setUpdatedAt(clone $now)
        ;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    public function setUuid(Uuid $uuid): void
    {
        $this->uuid = $uuid;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): void
    {
        $this->extension = $extension;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): void
    {
        $this->file = $file;
    }

    public function getFilePath(): string
    {
        return \sprintf(
            'images/%s.%s',
            $this->uuid,
            $this->extension
        );
    }

    public function syncWithUploadedFile(): void
    {
        if (!$this->file || !$this->file->isReadable()) {
            throw new \InvalidArgumentException('Invalid file');
        }

        $this->extension = $this->file->guessExtension();
    }

    public function __clone()
    {
        $this->uuid = Uuid::v4();
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $isDeleted): void
    {
        $this->deleted = $isDeleted;
    }
}
