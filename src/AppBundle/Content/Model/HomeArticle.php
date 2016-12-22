<?php

namespace AppBundle\Content\Model;

class HomeArticle
{
    private $type;
    private $title;
    private $subtitle;
    private $image;
    private $link;

    public function __construct(string $type, string $title, string $subtitle, string $image, string $link)
    {
        $this->type = $type;
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->image = $image;
        $this->link = $link;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSubtitle(): string
    {
        return $this->subtitle;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function getLink(): string
    {
        return $this->link;
    }
}
