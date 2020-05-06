<?php

namespace App\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="citizen_project_categories",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="citizen_project_category_name_unique", columns="name"),
 *         @ORM\UniqueConstraint(name="citizen_project_category_slug_unique", columns="slug")
 *     }
 * )
 *
 * @UniqueEntity("name")
 *
 * @Algolia\Index
 */
class CitizenProjectCategory extends BaseEventCategory
{
    /**
     * @var CitizenProjectCategorySkill[]|Collection
     *
     * @ORM\OneToMany(targetEntity="CitizenProjectCategorySkill", mappedBy="category", indexBy="id", cascade={"all"}, orphanRemoval=true)
     *
     * @Assert\Valid
     */
    private $categorySkills;

    public function __construct(?string $name = null, ?string $status = parent::DISABLED)
    {
        parent::__construct($name, $status);

        $this->categorySkills = new ArrayCollection();
    }

    public function addSkill(CitizenProjectSkill $skill, bool $promotion = false): void
    {
        foreach ($this->categorySkills as $categorySkill) {
            if ($categorySkill->getSkill() === $skill) {
                $categorySkill->setPromotion($promotion);

                return;
            }
        }

        $categorySkill = new CitizenProjectCategorySkill($this, $skill, $promotion);
        $this->categorySkills->add($categorySkill);
    }

    public function addCategorySkill(CitizenProjectCategorySkill $skill): void
    {
        if (!$this->categorySkills->contains($skill)) {
            $this->categorySkills->add($skill);
            $skill->setCategory($this);
        }
    }

    public function removeCategorySkill(CitizenProjectCategorySkill $skill): void
    {
        if ($this->categorySkills->contains($skill)) {
            $this->categorySkills->removeElement($skill);
        }
    }

    public function getCategorySkills(): Collection
    {
        return $this->categorySkills;
    }
}
