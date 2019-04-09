<?php

namespace AppBundle\Entity\ManagedArea;

use AppBundle\Entity\ReferentTag;
use Doctrine\ORM\Mapping as ORM;

trait ManagedTag
{
    /**
     * @var ReferentTag
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ReferentTag")
     */
    protected $tag;

    public function getTag(): ?ReferentTag
    {
        return $this->tag;
    }

    public function setTag(?ReferentTag $tag): void
    {
        $this->tag = $tag;
    }
}
