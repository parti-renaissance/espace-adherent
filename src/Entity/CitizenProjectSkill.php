<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CitizenProjectSkillRepository")
 * @ORM\Table(
 *   name="citizen_project_skills",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="citizen_project_skill_slug_unique", columns="slug")
 *   }
 * )
 *
 * @UniqueEntity("name")
 */
class CitizenProjectSkill
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
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(min=2, max=200)
     */
    private $name = '';

    /**
     * @var string|null
     *
     * @ORM\Column
     * @Gedmo\Slug(fields={"name"}, unique=true)
     */
    private $slug;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="CitizenProjectCategorySkill", mappedBy="citizen_project_skills", indexBy="id", cascade={"all"}, orphanRemoval=true)
     */
    private $categorySkills;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\CitizenProject", mappedBy="skills")
     *
     * @var CitizenProject[]
     */
    private $citizenProjects;

    public function __construct(?string $name = null)
    {
        $this->name = (string) $name;
        $this->categorySkills = new ArrayCollection();
        $this->citizenProjects = new ArrayCollection();
    }

    public function addCitizenProject(CitizenProject $citizenProjects): void
    {
        if (!$this->citizenProjects->contains($citizenProjects)) {
            $this->citizenProjects->add($citizenProjects);
        }
    }

    public function addCategory(CitizenProjectCategory $category, bool $promotion = false)
    {
        foreach ($this->categorySkills as $categorySkill) {
            if ($categorySkill->getCategory() === $category && $categorySkill->getPromotion() === $promotion) {
                return;
            }
        }

        $categorySkill = new CitizenProjectCategorySkill($category, $this, $promotion = false);
        $this->categorySkills->add($categorySkill);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
