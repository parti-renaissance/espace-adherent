<?php

namespace AppBundle\Entity\ChezVous;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ChezVous\MeasureTypeRepository")
 * @ORM\Table(name="chez_vous_measure_types")
 *
 * @Algolia\Index(autoIndex=false)
 *
 * @UniqueEntity("code")
 */
class MeasureType
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     * @Assert\Choice(callback={"AppBundle\ChezVous\MeasureChoiceLoader", "getTypeChoices"})
     *
     * @Algolia\Attribute
     */
    private $code;

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     *
     * @Algolia\Attribute
     */
    private $label;

    /**
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url
     */
    private $sourceLink;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255)
     */
    private $sourceLabel;

    public function __construct(string $code, string $label, string $sourceLink, string $sourceLabel)
    {
        $this->code = $code;
        $this->label = $label;
        $this->sourceLink = $sourceLink;
        $this->sourceLabel = $sourceLabel;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getSourceLink(): ?string
    {
        return $this->sourceLink;
    }

    public function setSourceLink(?string $sourceLink): void
    {
        $this->sourceLink = $sourceLink;
    }

    public function getSourceLabel(): ?string
    {
        return $this->sourceLabel;
    }

    public function setSourceLabel(?string $sourceLabel): void
    {
        $this->sourceLabel = $sourceLabel;
    }
}
