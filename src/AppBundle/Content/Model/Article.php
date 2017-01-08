<?php

namespace AppBundle\Content\Model;

class Article
{
    private $title;
    private $description;
    private $date;
    private $image;
    private $content;

    public function __construct(string $title, string $description, string $date, string $image, string $content)
    {
        $this->title = $title;
        $this->description = $description;
        $this->date = $date;
        $this->image = $image;
        $this->content = $content;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
