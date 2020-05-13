<?php

namespace App\React;

class PageMetaData implements PageMetaDataInterface
{
    private $title;
    private $description;
    private $imageWidth;
    private $imageHeight;
    private $imageUrl;

    public function __construct(
        string $title,
        string $description = null,
        int $imageWidth = null,
        int $imageHeight = null,
        string $imageUrl = null
    ) {
        $this->title = $title;
        $this->description = $description;
        $this->imageWidth = $imageWidth;
        $this->imageHeight = $imageHeight;
        $this->imageUrl = $imageUrl;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getImageWidth(): ?int
    {
        return $this->imageWidth;
    }

    public function getImageHeight(): ?int
    {
        return $this->imageHeight;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }
}
