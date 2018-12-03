<?php

namespace AppBundle\Entity\ManagedArea;

use AppBundle\Entity\ReferentTag;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait ManagedTag
{
    /**
     * @var ReferentTag|null
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ReferentTag")
     *
     * @Assert\NotNull
     */
    private $tag;

    public function getTag(): ?ReferentTag
    {
        return $this->tag;
    }

    public function setTag(ReferentTag $tag): void
    {
        $this->tag = $tag;
    }

    public function __toString(): string
    {
        return (string) $this->tag;
    }
}
