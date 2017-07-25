<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SkillRepository")
 * @ORM\Table(
 *   name="skills",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="skill_name_unique", columns="name")
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
     * @ORM\Column(unique=true)
     *
     * @Assert\NotBlank
     * @Assert\Length(min=2, max=200)
     */
    private $name = '';

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Summary", mappedBy="skills")
     *
     * @var Summary[]
     */
    private $summaries;

    public function __construct(?string $name = null)
    {
        if ($name) {
            $this->name = $name;
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
}
