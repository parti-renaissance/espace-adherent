<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CitizenProjectCategorySkillRepository")
 * @ORM\Table(name="citizen_project_category_skills")
 *
 * @UniqueEntity(
 *     fields={"category", "skill"},
 *     errorPath="skill",
 *     message="This skill is already in use on that category."
 * )
 *
 * @Algolia\Index(autoIndex=false)
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
     * @ORM\ManyToOne(targetEntity="CitizenProjectCategory", inversedBy="categorySkills")
     */
    private $category;

    /**
     * @var CitizenProjectSkill
     *
     * @ORM\ManyToOne(targetEntity="CitizenProjectSkill")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $skill;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $promotion;

    public function __construct(
        ?CitizenProjectCategory $category = null,
        ?CitizenProjectSkill $skill = null,
        ?bool $promotion = false
    ) {
        $this->category = $category;
        $this->skill = $skill;
        $this->promotion = $promotion;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): ?CitizenProjectCategory
    {
        return $this->category;
    }

    public function setCategory(CitizenProjectCategory $category): void
    {
        $this->category = $category;
    }

    public function getSkill(): ?CitizenProjectSkill
    {
        return $this->skill;
    }

    public function setSkill(CitizenProjectSkill $skill): void
    {
        $this->skill = $skill;
    }

    public function getPromotion(): ?bool
    {
        return $this->promotion;
    }

    public function setPromotion(bool $promotion): void
    {
        $this->promotion = $promotion;
    }
}
