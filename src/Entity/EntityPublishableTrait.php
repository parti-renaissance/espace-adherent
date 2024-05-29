<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait EntityPublishableTrait
{
    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    private $published = false;

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published)
    {
        $this->published = $published;

        return $this;
    }
}
