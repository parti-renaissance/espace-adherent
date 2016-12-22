<?php

namespace AppBundle\Content\Model;

class HomeLiveLink
{
    private $title;
    private $link;

    public function __construct(string $title, string $link)
    {
        $this->title = $title;
        $this->link = $link;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getLink(): string
    {
        return $this->link;
    }
}
