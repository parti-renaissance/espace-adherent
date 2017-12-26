<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SkillRepository")
 * @ORM\Table(
 *   name="skills",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="skill_slug_unique", columns="slug")
 *   }
 * )
 *
 * @UniqueEntity("name")
 */
class Skill extends BaseSkill
{
    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Summary", mappedBy="skills")
     *
     * @var Summary[]
     */
    private $summaries;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\CitizenInitiative", mappedBy="skills")
     *
     * @var CitizenInitiative[]
     */
    private $citizenInitiatives;

    public function __construct(?string $name = null)
    {
        parent::__construct($name);

        $this->summaries = new ArrayCollection();
        $this->citizenInitiatives = new ArrayCollection();
    }

    public function addSummary(Summary $summary): void
    {
        if (!$this->summaries->contains($summary)) {
            $this->summaries->add($summary);
        }
    }

    public function addCitizenInitiative(CitizenInitiative $initiative): void
    {
        if (!$this->citizenInitiatives->contains($initiative)) {
            $this->citizenInitiatives->add($initiative);
        }
    }
}
