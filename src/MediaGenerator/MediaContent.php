<?php

namespace App\MediaGenerator;

class MediaContent
{
    private $content;
    private $mimeType;
    private $size;

    public function __construct(string $content, string $mimeType, int $size)
    {
        $this->content = $content;
        $this->mimeType = $mimeType;
        $this->size = $size;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getContentAsDataUrl(): string
    {
        return sprintf('data:%s;base64,%s', $this->getMimeType(), base64_encode($this->getContent()));
    }
}
