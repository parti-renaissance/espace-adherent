<?php

namespace App\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;

trait EntityPublishableTrait
{
    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $published = false;

    /**
     * @Algolia\IndexIf
     */
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
