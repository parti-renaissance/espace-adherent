<?php

namespace App\Documents;

class Document
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $extension;

    /**
     * @var string
     */
    private $path;

    public function __construct(string $type, string $name, string $extension, string $path)
    {
        $this->type = $type;
        $this->name = $name;
        $this->extension = $extension;
        $this->path = $path;
    }

    /**
     * Return the Font Awesome icon.
     */
    public function getIcon(): string
    {
        if ('dir' === $this->type) {
            return 'folder-open-o';
        }

        return 'file-o';
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getName(): string
    {
        if ('dir' === $this->type) {
            return $this->name;
        }

        return $this->name.'.'.$this->extension;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
