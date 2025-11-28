<?php

declare(strict_types=1);

namespace App\Api\DTO;

use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class ImageContent
{
    /**
     * @var string
     */
    #[Assert\NotBlank]
    private $content;

    /**
     * @var UploadedFile|null
     */
    #[Assert\Image(maxSize: '5M', mimeTypes: ['image/jpeg', 'image/png', 'image/webp'])]
    #[Assert\NotBlank]
    private $file;

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;

        $this->loadFile();
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function getBinaryContent(): string
    {
        if (str_contains($this->content, 'base64,')) {
            return base64_decode(explode('base64,', $this->content, 2)[1]);
        }

        return $this->content;
    }

    public function getExtension(): string
    {
        return explode('/', $this->getMimeType())[1];
    }

    public function getMimeType(): string
    {
        if (str_starts_with($this->content, 'data:')) {
            return substr($this->content, 5, strpos($this->content, ';') - 5);
        }

        return 'image/png';
    }

    private function loadFile(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), uniqid());
        file_put_contents($tmpFile, $this->getBinaryContent());

        $this->file = new UploadedFile(
            $tmpFile,
            Uuid::uuid4().'.'.$this->getExtension(),
            $this->getMimeType(),
            null,
            true
        );
    }
}
