<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CitizenProjectCategorySkillRepository")
 * @ORM\Table(
 *   name="citizen_project_category_skills",
 * )
 *
 * @UniqueEntity("name")
 */
class CitizenProjectCategorySkill
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var CitizenProjectCategory
     *
     * @ORM\ManyToOne(targetEntity="CitizenProjectCategory", inversedBy="citizen_project_categories")
     */
    private $category;

    /**
     * @var CitizenProjectSkill
     *
     * @ORM\ManyToOne(targetEntity="CitizenProjectSkill", inversedBy="citizen_project_skills")
     */
    private $skill;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $promotion;

    public function __construct(CitizenProjectCategory $category, CitizenProjectSkill $skill, bool $promotion = false)
    {
        $this->category = $category;
        $this->skill = $skill;
        $this->promotion = $promotion;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory(CitizenProjectCategory $category): CitizenProjectCategory
    {
        $this->category = $category;
    }

    public function getSkill(): CitizenProjectSkill
    {
        return $this->skill;
    }

    public function setSkill(CitizenProjectSkill $skill)
    {
        $this->skill = $skill;
    }

    public function getPromotion(): bool
    {
        return $this->promotion;
    }

    public function setPromotion(bool $promotion): void
    {
        $this->promotion = $promotion;
    }
}
