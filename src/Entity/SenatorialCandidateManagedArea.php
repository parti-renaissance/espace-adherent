<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'senatorial_candidate_areas')]
class SenatorialCandidateManagedArea
{
    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    #[ORM\InverseJoinColumn(name: 'referent_tag_id', referencedColumnName: 'id')]
    #[ORM\JoinColumn(name: 'senatorial_candidate_area_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\JoinTable(name: 'senatorial_candidate_areas_tags')]
    #[ORM\ManyToMany(targetEntity: ReferentTag::class, cascade: ['persist'])]
    private $departmentTags;

    public function __construct()
    {
        $this->departmentTags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDepartmentTags(): iterable
    {
        return $this->departmentTags;
    }

    public function addDepartmentTag(ReferentTag $tag): void
    {
        if (!$this->departmentTags->contains($tag)) {
            $this->departmentTags->add($tag);
        }
    }

    public function removeDepartmentTag(ReferentTag $tag): void
    {
        $this->departmentTags->removeElement($tag);
    }
}
