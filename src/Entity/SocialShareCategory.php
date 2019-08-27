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
    use PositionTrait;

    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(length=100)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=100)
     */
    private $name;

    /**
     * @ORM\Column
     *
     * @Gedmo\Slug(fields={"name"})
     */
    private $slug;

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

    public function setName(?string $name): void
    {
        $this->name = (string) $name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }
}
