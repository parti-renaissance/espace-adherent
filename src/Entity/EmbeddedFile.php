<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class EmbeddedFile
{
    #[ORM\Column(nullable: true)]
    protected ?string $name = null;

    #[ORM\Column(nullable: true)]
    protected ?string $originalName = null;

    #[ORM\Column(nullable: true)]
    protected ?string $mimeType = null;

    #[ORM\Column(nullable: true)]
    protected ?int $size = null;

    #[ORM\Column(type: 'simple_array', nullable: true)]
    protected ?array $dimensions = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    public function setOriginalName(?string $originalName): void
    {
        $this->originalName = $originalName;
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

    public function getDimensions(): ?array
    {
        return $this->dimensions;
    }

    public function setDimensions(?array $dimensions): void
    {
        $this->dimensions = $dimensions;
    }

    public function getWidth(): ?int
    {
        return $this->dimensions[0] ?? null;
    }

    public function getHeight(): ?int
    {
        return $this->dimensions[1] ?? null;
    }

    public function isEmpty(): bool
    {
        return empty($this->name) && empty($this->size);
    }
}
