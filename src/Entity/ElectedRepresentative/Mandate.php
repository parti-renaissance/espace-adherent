<?php

namespace App\Entity\ElectedRepresentative;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\EntityIdentityTrait;
use App\Entity\Geo\Zone as GeoZone;
use App\Repository\ElectedRepresentative\MandateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/elected_mandates/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'elected_representative')"
        ),
        new Put(
            uriTemplate: '/elected_mandates/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'elected_representative')"
        ),
        new Delete(
            uriTemplate: '/elected_mandates/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'elected_representative')"
        ),
        new Post(uriTemplate: '/elected_mandates'),
    ],
    routePrefix: '/v3',
    normalizationContext: ['groups' => ['elected_mandate_read']],
    denormalizationContext: ['groups' => ['elected_mandate_write']],
    security: "is_granted('REQUEST_SCOPE_GRANTED', 'elected_representative')"
)]
#[ORM\Entity(repositoryClass: MandateRepository::class)]
#[ORM\Table(name: 'elected_representative_mandate')]
class Mandate
{
    use EntityIdentityTrait;

    /**
     * @var string
     */
    #[Assert\Choice(callback: [MandateTypeEnum::class, 'toArray'])]
    #[Assert\NotBlank]
    #[Groups(['elected_mandate_write', 'elected_mandate_read', 'elected_representative_read', 'elected_representative_list'])]
    #[ORM\Column]
    private $type;

    /**
     * @var bool
     */
    #[Groups(['elected_mandate_write', 'elected_mandate_read', 'elected_representative_read'])]
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public $isElected;

    /**
     * @var Zone|null
     *
     * @deprecated Will be replaced by $geoZone
     */
    #[ORM\ManyToOne(targetEntity: Zone::class)]
    private $zone;

    /**
     * @var GeoZone|null
     */
    #[Assert\Expression("value !== null or (value == null and this.getType() === constant('App\\\\Entity\\\\ElectedRepresentative\\\\MandateTypeEnum::EURO_DEPUTY'))", message: 'Le périmètre géographique est obligatoire.')]
    #[Groups(['elected_mandate_write', 'elected_mandate_read', 'elected_representative_read', 'elected_representative_list'])]
    #[ORM\ManyToOne(targetEntity: GeoZone::class)]
    private $geoZone;

    /**
     * @var bool
     */
    #[Groups(['elected_mandate_write', 'elected_mandate_read', 'elected_representative_read'])]
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private $onGoing = true;

    /**
     * @var \DateTime
     */
    #[Assert\NotBlank]
    #[Groups(['elected_mandate_write', 'elected_mandate_read', 'elected_representative_read'])]
    #[ORM\Column(type: 'date')]
    private $beginAt;

    /**
     * @var \DateTime|null
     */
    #[Assert\Expression('value === null or value > this.getBeginAt()', message: 'La date de fin du mandat doit être postérieure à la date de début.')]
    #[Assert\Expression('not (value !== null and this.isOnGoing())', message: "La date de fin ne peut être saisie que dans le cas où le mandat n'est pas en cours.")]
    #[Groups(['elected_mandate_write', 'elected_mandate_read', 'elected_representative_read'])]
    #[ORM\Column(type: 'date', nullable: true)]
    private $finishAt;

    /**
     * @var string
     */
    #[Groups(['elected_mandate_write', 'elected_mandate_read', 'elected_representative_read'])]
    #[ORM\Column(length: 10, nullable: true)]
    private $politicalAffiliation;

    /**
     * @var string|null
     */
    #[Assert\Choice(callback: [LaREMSupportEnum::class, 'toArray'])]
    #[Groups(['elected_mandate_write', 'elected_mandate_read', 'elected_representative_read'])]
    #[ORM\Column(nullable: true)]
    private $laREMSupport;

    /**
     * @var ElectedRepresentative
     */
    #[Assert\NotBlank]
    #[Groups(['elected_mandate_write', 'elected_mandate_read'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: ElectedRepresentative::class, inversedBy: 'mandates')]
    private $electedRepresentative;

    /**
     * @var PoliticalFunction[]|Collection
     */
    #[Assert\Valid]
    #[Groups(['elected_mandate_write', 'elected_mandate_read', 'elected_representative_read'])]
    #[ORM\OneToMany(mappedBy: 'mandate', targetEntity: PoliticalFunction::class, cascade: ['all'], orphanRemoval: true)]
    private $politicalFunctions;

    /**
     * @var int
     */
    #[ORM\Column(type: 'smallint', options: ['default' => 1])]
    private $number = 1;

    #[ORM\ManyToOne(targetEntity: GeoZone::class)]
    private ?GeoZone $attachedZone;

    public function __construct(
        ?UuidInterface $uuid = null,
        ?string $type = null,
        bool $isElected = false,
        ?string $politicalAffiliation = null,
        ?string $laREMSupport = null,
        ?GeoZone $geoZone = null,
        ?ElectedRepresentative $electedRepresentative = null,
        bool $onGoing = true,
        ?\DateTime $beginAt = null,
        ?\DateTime $finishAt = null,
        ?GeoZone $attachedZone = null,
    ) {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->type = $type;
        $this->isElected = $isElected;
        $this->geoZone = $geoZone;
        $this->electedRepresentative = $electedRepresentative;
        $this->laREMSupport = $laREMSupport;
        $this->politicalAffiliation = $politicalAffiliation;
        $this->onGoing = $onGoing;
        $this->beginAt = $beginAt;
        $this->finishAt = $finishAt;
        $this->attachedZone = $attachedZone;

        $this->politicalFunctions = new ArrayCollection();
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getName(): string
    {
        return (string) array_search($this->type, MandateTypeEnum::CHOICES);
    }

    public function setType(?string $type): void
    {
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

    /**
     * @deprecated Will be replace by getGeoZone()
     */
    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    /**
     * @deprecated Will be replace by setGeoZone()
     */
    public function setZone(?Zone $zone): void
    {
        $this->zone = $zone;
    }

    public function getGeoZone(): ?GeoZone
    {
        return $this->geoZone;
    }

    public function setGeoZone(?GeoZone $geoZone): void
    {
        $this->geoZone = $geoZone;
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

    public function setPoliticalAffiliation(?string $politicalAffiliation): void
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

    public function setNumber(?int $number): void
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

    public function getLastPoliticalFunction(): ?PoliticalFunction
    {
        if (0 === $this->politicalFunctions->count()) {
            return null;
        }

        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('onGoing', true))
            ->orderBy(['beginAt' => 'DESC'])
        ;

        $functions = $this->politicalFunctions->matching($criteria);

        return $functions->count() > 0 ? $functions->first() : null;
    }

    public function getAttachedZone(): ?GeoZone
    {
        return $this->attachedZone;
    }

    public function setAttachedZone(?GeoZone $attachedZone): void
    {
        $this->attachedZone = $attachedZone;
    }

    public function __toString(): string
    {
        $str = array_search($this->type, MandateTypeEnum::CHOICES);

        if (!$this->politicalAffiliation) {
            return $str;
        }

        return \sprintf('%s (%s)', $str, $this->politicalAffiliation);
    }
}
