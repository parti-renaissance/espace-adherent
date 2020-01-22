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
     * @var ReferentTag[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\ReferentTag")
     */
    private $districtTag;

    public function __construct()
    {
        $this->districtTag = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDistrictTags(): ArrayCollection
    {
        return $this->districtTag;
    }

    public function addDistrictTag(ReferentTag $tag): void
    {
        if (!$this->districtTag->contains($tag)) {
            $this->districtTag->add($tag);
        }
    }

    public function removeDistrictTag(ReferentTag $tag): void
    {
        if ($this->districtTag->contains($tag)) {
            $this->districtTag->remove($tag);
        }
    }
}
