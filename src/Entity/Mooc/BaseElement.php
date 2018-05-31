<?php

namespace AppBundle\Entity\Mooc;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\EntityTimestampableTrait;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="mooc_elements")
 *
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "video": "AppBundle\Entity\Mooc\Video",
 * })
 *
 * @Algolia\Index(autoIndex=false)
 */
abstract class BaseElement
{
    use EntityTimestampableTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    protected $title;

    /**
     * @ORM\Column
     * @Gedmo\Slug(fields={"title"}, unique=true)
     */
    protected $slug;

    /**
     * @ORM\Column(type="smallint", options={"default": 1})
     */
    protected $displayOrder = 1;

    public function __toString()
    {
        return (string) $this->title;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }

    public function getDisplayOrder(): ?int
    {
        return $this->displayOrder;
    }

    public function setDisplayOrder(int $displayOrder): void
    {
        $this->displayOrder = $displayOrder;
    }
}
