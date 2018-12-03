<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="communication_manager_areas")
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class CommunicationManagerArea
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var ReferentTag[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\ReferentTag")
     * @ORM\JoinTable(
     *     name="communication_manager_areas_tags",
     *     joinColumns={
     *         @ORM\JoinColumn(name="communication_manager_area_id", referencedColumnName="id", onDelete="CASCADE")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="referent_tag_id", referencedColumnName="id")
     *     }
     * )
     */
    private $tags;

    public function __construct(array $tags = [])
    {
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

    public function getReferentTagCodes(): array
    {
        return array_map(function (ReferentTag $tag) { return $tag->getCode(); }, $this->getTags()->toArray());
    }
}
