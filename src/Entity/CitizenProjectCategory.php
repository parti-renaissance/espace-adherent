<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(
 *   name="citizen_project_categories",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="citizen_project_category_name_unique", columns="name")
 *   }
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
     * @ORM\OneToMany(targetEntity="CitizenProjectCategorySkill", mappedBy="citizen_project_categories", indexBy="id", cascade={"all"}, orphanRemoval=true)
     */
    private $categorySkills;

    public function __construct(?string $name = null, ?string $status = parent::DISABLED)
    {
        parent::__construct($name, $status);

        $this->categorySkills = new ArrayCollection();
    }

    public function addSkill(CitizenProjectSkill $skill, bool $promotion = false)
    {
        foreach ($this->categorySkills as $categorySkill) {
            if ($categorySkill->getSkill() === $skill && $categorySkill->getPromotion() === $promotion) {
                return;
            }
        }

        $categorySkill = new CitizenProjectCategorySkill($this, $skill, $promotion);
        $this->categorySkills->add($categorySkill);
    }
}
