<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class ConsularManagedArea
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var ConsularDistrict[]|ArrayCollection
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ConsularDistrict")
     */
    private $consularDistrict;

    public function __construct()
    {
        $this->consularDistrict = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getConsularDistricts(): ArrayCollection
    {
        return $this->consularDistrict;
    }

    public function addConsularDistrict(ReferentTag $tag): void
    {
        if (!$this->consularDistrict->contains($tag)) {
            $this->consularDistrict->add($tag);
        }
    }

    public function removeConsularDistrict(ReferentTag $tag): void
    {
        if ($this->consularDistrict->contains($tag)) {
            $this->consularDistrict->remove($tag);
        }
    }
}
