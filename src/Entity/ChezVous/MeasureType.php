<?php

namespace App\Entity\ChezVous;

use App\Repository\ChezVous\MeasureTypeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueEntity("code")
 */
#[ORM\Table(name: 'chez_vous_measure_types')]
#[ORM\Entity(repositoryClass: MeasureTypeRepository::class)]
class MeasureType
{
    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    private $id;

    /**
     * @var string|null
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     * @Assert\Choice(callback={"App\ChezVous\MeasureChoiceLoader", "getTypeChoices"})
     */
    #[ORM\Column(unique: true)]
    private $code;

    /**
     * @var string|null
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    #[ORM\Column]
    private $label;

    /**
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: 'datetime')]
    private $updatedAt;

    /**
     * @var string|null
     *
     * @Assert\Url
     */
    #[ORM\Column(nullable: true)]
    private $sourceLink;

    /**
     * @var string|null
     *
     * @Assert\Length(max=255)
     */
    #[ORM\Column(nullable: true)]
    private $sourceLabel;

    /**
     * @var string|null
     *
     * @Assert\Url
     */
    #[ORM\Column(nullable: true)]
    private $oldolfLink;

    /**
     * @var string|null
     *
     * @Assert\Url
     */
    #[ORM\Column(nullable: true)]
    private $eligibilityLink;

    public function __construct(string $code, string $label)
    {
        $this->code = $code;
        $this->label = $label;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function __toString()
    {
        return $this->label;
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

    public function getOldolfLink(): ?string
    {
        return $this->oldolfLink;
    }

    public function setOldolfLink(?string $oldolfLink): void
    {
        $this->oldolfLink = $oldolfLink;
    }

    public function getEligibilityLink(): ?string
    {
        return $this->eligibilityLink;
    }

    public function setEligibilityLink(?string $eligibilityLink): void
    {
        $this->eligibilityLink = $eligibilityLink;
    }
}
