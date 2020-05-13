<?php

namespace App\ValueObject;

final class Link
{
    private $url;
    private $label;

    public function __construct(string $url, string $label = null)
    {
        $this->url = $url;
        $this->label = $label;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }
}
