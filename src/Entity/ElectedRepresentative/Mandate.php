<?php

namespace AppBundle\Entity\ElectedRepresentative;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Exception\BadMandateTypeException;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="elected_representative_mandate")
 *
 * @Algolia\Index(autoIndex=false)
 */
class Mandate
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Choice(callback={"AppBundle\Entity\ElectedRepresentative\MandateTypeEnum", "toArray"})
     */
    private $type;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isElected;

    /**
     * @var string
     *
     * @ORM\Column(length=255)
     *
     * @Assert\NotBlank
     * @Assert\Length(max="255")
     */
    private $geographicalArea;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date")
     *
     * @Assert\NotBlank
     * @Assert\DateTime
     */
    private $beginAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="date", nullable=true)
     *
     * @Assert\DateTime
     * @Assert\Expression("value == null or value > this.getBeginAt()", message="La date de fin du mandat doit être postérieure à la date de début.")
     */
    private $finishAt;

    /**
     * @var string
     *
     * @ORM\Column(length=10)
     *
     * @Assert\NotBlank
     * @Assert\Length(max="10")
     */
    private $politicalAffiliation;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Choice(callback={"AppBundle\Entity\ElectedRepresentative\LaREMSupportEnum", "toArray"})
     */
    private $laREMSupport;

    /**
     * @var ElectedRepresentative
     *
     * @ORM\ManyToOne(targetEntity="ElectedRepresentative", inversedBy="mandates")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Assert\NotBlank
     */
    private $electedRepresentative;

    public function __construct(
        string $type = null,
        bool $isElected = false,
        string $geographicalArea = null,
        string $politicalAffiliation = null,
        string $laREMSupport = null,
        ElectedRepresentative $electedRepresentative = null,
        \DateTime $beginAt = null,
        \DateTime $finishAt = null
    ) {
        $this->type = $type;
        $this->isElected = $isElected;
        $this->geographicalArea = $geographicalArea;
        $this->electedRepresentative = $electedRepresentative;
        $this->laREMSupport = $laREMSupport;
        $this->politicalAffiliation = $politicalAffiliation;
        $this->beginAt = $beginAt;
        $this->finishAt = $finishAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        if (!MandateTypeEnum::isValid($type)) {
            throw new BadMandateTypeException(sprintf('The mandate type "%s" is invalid', $type));
        }

        $this->type = $type;
    }

    public function isElected(): bool
    {
        return $this->isElected;
    }

    public function setIsElected(bool $isElected): void
    {
        $this->isElected = $isElected;
    }

    public function getGeographicalArea(): ?string
    {
        return $this->geographicalArea;
    }

    public function setGeographicalArea(string $geographicalArea): void
    {
        $this->geographicalArea = $geographicalArea;
    }

    public function getBeginAt(): ?\DateTime
    {
        return $this->beginAt;
    }

    public function setBeginAt(\DateTime $beginAt): void
    {
        $this->beginAt = $beginAt;
    }

    public function getFinishAt(): ?\DateTime
    {
        return $this->finishAt;
    }

    public function setFinishAt(?\DateTime $finishAt = null): void
    {
        $this->finishAt = $finishAt;
    }

    public function getPoliticalAffiliation(): ?string
    {
        return $this->politicalAffiliation;
    }

    public function setPoliticalAffiliation(string $politicalAffiliation): void
    {
        $this->politicalAffiliation = $politicalAffiliation;
    }

    public function getLaREMSupport(): ?string
    {
        return $this->laREMSupport;
    }

    public function setLaREMSupport(?string $laREMSupport = null): void
    {
        $this->laREMSupport = $laREMSupport;
    }

    public function getElectedRepresentative(): ?ElectedRepresentative
    {
        return $this->electedRepresentative;
    }

    public function setElectedRepresentative(ElectedRepresentative $electedRepresentative): void
    {
        $this->electedRepresentative = $electedRepresentative;
    }

    public function __toString(): string
    {
        return sprintf('%s (%s)', array_search($this->type, MandateTypeEnum::CHOICES), $this->politicalAffiliation);
    }
}
