<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SocialShareCategoryRepository")
 * @ORM\Table(name="social_share_categories")
 *
 * @Algolia\Index(autoIndex=false)
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
        $this->name = $name;
        $this->position = $position;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(?string $name)
    {
        $this->name = (string) $name;
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
