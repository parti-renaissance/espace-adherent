<?php

namespace App\Entity\ElectedRepresentative;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Exception\BadMandateTypeException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ElectedRepresentative\MandateRepository")
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
     * @Assert\Choice(callback={"App\Entity\ElectedRepresentative\MandateTypeEnum", "toArray"})
     */
    private $type;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isElected;

    /**
     * @var Zone|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ElectedRepresentative\Zone", cascade={"merge", "detach"})
     * @ORM\JoinColumn
     *
     * @Assert\Expression(
     *     "value !== null or (value == null and this.getType() === constant('App\\Entity\\ElectedRepresentative\\MandateTypeEnum::EURO_DEPUTY'))",
     *     message="Le périmètre géographique est obligatoire."
     * )
     */
    private $zone;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private $onGoing = true;

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
     * @Assert\Expression(
     *     "value === null or value > this.getBeginAt()",
     *     message="La date de fin du mandat doit être postérieure à la date de début."
     * )
     * @Assert\Expression(
     *     "(value === null and (!this.isElected() or this.isOnGoing())) or (value !== null and this.isElected() and !this.isOnGoing())",
     *     message="La date de fin peut être saisie que dans le cas où le mandat n'est pas en cours et est élu."
     * )
     */
    private $finishAt;

    /**
     * @var string
     *
     * @ORM\Column(length=10)
     *
     * @Assert\NotBlank
     * @Assert\Choice(callback={"App\Election\VoteListNuanceEnum", "toArray"})
     */
    private $politicalAffiliation;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Choice(callback={"App\Entity\ElectedRepresentative\LaREMSupportEnum", "toArray"})
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

    /**
     * @var PoliticalFunction[]|Collection
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\ElectedRepresentative\PoliticalFunction",
     *     mappedBy="mandate",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     *
     * @Assert\Valid
     */
    private $politicalFunctions;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", options={"default": 1})
     */
    private $number = 1;

    public function __construct(
        string $type = null,
        bool $isElected = false,
        string $politicalAffiliation = null,
        string $laREMSupport = null,
        Zone $zone = null,
        ElectedRepresentative $electedRepresentative = null,
        bool $onGoing = true,
        \DateTime $beginAt = null,
        \DateTime $finishAt = null
    ) {
        $this->type = $type;
        $this->isElected = $isElected;
        $this->zone = $zone;
        $this->electedRepresentative = $electedRepresentative;
        $this->laREMSupport = $laREMSupport;
        $this->politicalAffiliation = $politicalAffiliation;
        $this->onGoing = $onGoing;
        $this->beginAt = $beginAt;
        $this->finishAt = $finishAt;

        $this->politicalFunctions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getName(): string
    {
        return (string) array_search($this->type, MandateTypeEnum::CHOICES);
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

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(Zone $zone): void
    {
        $this->zone = $zone;
    }

    public function isOnGoing(): bool
    {
        return $this->onGoing;
    }

    public function setOnGoing(bool $onGoing): void
    {
        $this->onGoing = $onGoing;
    }

    public function getBeginAt(): ?\DateTime
    {
        return $this->beginAt;
    }

    public function setBeginAt(?\DateTime $beginAt): void
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

    public function getNumber(): int
    {
        return $this->number;
    }

    public function setNumber(int $number): void
    {
        $this->number = $number;
    }

    public function addPoliticalFunction(PoliticalFunction $politicalFunction): void
    {
        if (!$this->politicalFunctions->contains($politicalFunction)) {
            $this->politicalFunctions->add($politicalFunction);
            $politicalFunction->setMandate($this);
        }
    }

    public function removePoliticalFunction(PoliticalFunction $politicalFunction): void
    {
        $this->politicalFunctions->removeElement($politicalFunction);
    }

    public function getPoliticalFunctions(): Collection
    {
        return $this->politicalFunctions;
    }

    public function __toString(): string
    {
        return sprintf('%s (%s)', array_search($this->type, MandateTypeEnum::CHOICES), $this->politicalAffiliation);
    }
}
