<?php

namespace AppBundle\Entity\IdeasWorkshop;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use ApiPlatform\Core\Annotation\ApiResource;
use AppBundle\Entity\EntityPublishableTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     collectionOperations={},
 *     itemOperations={"get"},
 * )
 *
 * @ORM\Table(name="ideas_workshop_question")
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class Question
{
    use EntityPublishableTrait;

    /**
     * @var int
     *
     * @SymfonySerializer\Groups("idea_read")
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     *
     * @SymfonySerializer\Groups("guideline_read")
     */
    private $id;

    /**
     * @var Guideline
     *
     * @ORM\ManyToOne(targetEntity="Guideline", inversedBy="questions")
     */
    private $guideline;

    /**
     * @ORM\Column
     *
     * @SymfonySerializer\Groups("guideline_read")
     */
    private $placeholder;

    /**
     * @Assert\GreaterThanOrEqual(0)
     *
     * @Gedmo\SortablePosition
     *
     * @ORM\Column(type="smallint", options={"unsigned": true})
     *
     * @SymfonySerializer\Groups("guideline_read")
     */
    private $position;

    /**
     * @ORM\Column
     *
     * @SymfonySerializer\Groups("guideline_read")
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     *
     * @SymfonySerializer\Groups("guideline_read")
     */
    private $required;

    public function __construct(
        string $name,
        string $placeholder,
        int $position = 0,
        bool $required = false,
        bool $published = true
    ) {
        $this->name = $name;
        $this->position = $position;
        $this->placeholder = $placeholder;
        $this->published = $published;
        $this->required = $required;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGuideline(): Guideline
    {
        return $this->guideline;
    }

    public function setGuideline(Guideline $guideline): void
    {
        $this->guideline = $guideline;
    }

    public function getPlaceholder(): string
    {
        return $this->placeholder;
    }

    public function setPlaceholder(string $placeholder): void
    {
        $this->placeholder = $placeholder;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): void
    {
        $this->required = $required;
    }
}
