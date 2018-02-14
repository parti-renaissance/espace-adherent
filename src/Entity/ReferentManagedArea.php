<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="referent_managed_areas")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProcurationManagerRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class ReferentManagedArea
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var ReferentTag[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\ReferentTag")
     * @ORM\JoinTable(
     *     name="referent_managed_areas_tags",
     *     joinColumns={
     *         @ORM\JoinColumn(name="referent_managed_area_id", referencedColumnName="id", onDelete="CASCADE")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="referent_tag_id", referencedColumnName="id")
     *     }
     * )
     */
    private $tags;

    /**
     * @ORM\Column(type="geo_point", nullable=true)
     */
    private $markerLatitude;

    /**
     * @ORM\Column(type="geo_point", nullable=true)
     */
    private $markerLongitude;

    public function __construct(
        array $tags = [],
        string $latitude = null,
        string $longitude = null
    ) {
        $this->markerLatitude = $latitude;
        $this->markerLongitude = $longitude;
        $this->tags = new ArrayCollection($tags);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(ReferentTag $tag): void
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }
    }

    public function removeTag(ReferentTag $tag): void
    {
        $this->tags->removeElement($tag);
    }

    public function getMarkerLatitude(): ?string
    {
        return $this->markerLatitude;
    }

    public function setMarkerLatitude(?string $markerLatitude): void
    {
        if (!$markerLatitude) {
            $markerLatitude = null;
        }

        $this->markerLatitude = $markerLatitude;
    }

    public function getMarkerLongitude(): ?string
    {
        return $this->markerLongitude;
    }

    public function setMarkerLongitude(?string $markerLongitude): void
    {
        if (!$markerLongitude) {
            $markerLongitude = null;
        }

        $this->markerLongitude = $markerLongitude;
    }
}
