<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

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
class Skill
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
        $this->name = (string) $name;
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
}
