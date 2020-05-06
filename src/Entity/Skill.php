<?php

namespace App\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SkillRepository")
 * @ORM\Table(
 *     name="skills",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="skill_slug_unique", columns="slug")
 *     }
 * )
 *
 * @UniqueEntity("name")
 *
 * @Algolia\Index(autoIndex=false)
 */
class Skill extends BaseSkill
{
    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Summary", mappedBy="skills")
     *
     * @var Summary[]
     */
    private $summaries;

    public function __construct(?string $name = null)
    {
        parent::__construct($name);

        $this->summaries = new ArrayCollection();
    }

    public function addSummary(Summary $summary): void
    {
        if (!$this->summaries->contains($summary)) {
            $this->summaries->add($summary);
        }
    }
}
