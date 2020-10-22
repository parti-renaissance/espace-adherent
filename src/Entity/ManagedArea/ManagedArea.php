<?php

namespace App\Entity\ManagedArea;

use App\Entity\ReferentTag;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "regional_candidate": "App\Entity\ManagedArea\RegionalCandidateManagedArea",
 *     "departmental_candidate": "App\Entity\ManagedArea\DepartmentalCandidateManagedArea",
 *     "tdl_departmental_candidate": "App\Entity\ManagedArea\TdlDepartmentalCandidateManagedArea",
 * })
 */
abstract class ManagedArea
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\ReferentTag", cascade={"persist"})
     * @ORM\JoinTable(
     *     joinColumns={
     *         @ORM\JoinColumn(name="managed_area_id", referencedColumnName="id", onDelete="CASCADE")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="referent_tag_id", referencedColumnName="id")
     *     }
     * )
     */
    private $referentTags;

    public function __construct()
    {
        $this->referentTags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReferentTags(): iterable
    {
        return $this->referentTags;
    }

    public function addReferentTag(ReferentTag $tag): void
    {
        if (!$this->referentTags->contains($tag)) {
            $this->referentTags->add($tag);
        }
    }

    public function removeReferentTag(ReferentTag $tag): void
    {
        $this->referentTags->removeElement($tag);
    }
}
