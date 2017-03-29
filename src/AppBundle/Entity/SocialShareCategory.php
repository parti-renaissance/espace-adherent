<?php

namespace AppBundle\Entity;

use AppBundle\Utils\EmojisRemover;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="social_share_categories")
 */
class SocialShareCategory
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(length=100)
     *
     * @Assert\NotBlank
     * @Assert\Length(max = 100)
     */
    private $name;

    /**
     * @ORM\Column
     *
     * @Gedmo\Slug(fields={"name"})
     */
    private $slug;

    /**
     * @ORM\Column(type="integer")
     */
    private $position;

    public function __construct(string $name = '', int $position = 1)
    {
        $this->name = EmojisRemover::remove($name);
        $this->position = $position;
    }

    public function __toString()
    {
        return $this->name ?: '';
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name)
    {
        $this->name = EmojisRemover::remove($name);
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(?int $position)
    {
        $this->position = $position;
    }
}
