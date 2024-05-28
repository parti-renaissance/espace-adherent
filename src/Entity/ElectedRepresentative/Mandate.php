<?php

namespace App\Entity\ElectedRepresentative;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\EntityIdentityTrait;
use App\Entity\Geo\Zone as GeoZone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     routePrefix="/v3",
 *     attributes={
 *         "normalization_context": {
 *             "groups": {"elected_mandate_read"}
 *         },
 *         "denormalization_context": {
 *             "groups": {"elected_mandate_write"}
 *         },
 *         "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'elected_representative')"
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/elected_mandates/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'elected_representative')"
 *         },
 *         "put": {
 *             "path": "/elected_mandates/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'elected_representative')"
 *         },
 *         "delete": {
 *             "path": "/elected_mandates/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'elected_representative')"
 *         }
 *     },
 *     collectionOperations={
 *         "post": {
 *             "path": "/elected_mandates",
 *         }
 *     }
 * )
 *
 * @ORM\Entity(repositoryClass="App\Repository\ElectedRepresentative\MandateRepository")
 * @ORM\Table(name="elected_representative_mandate")
 */
class Mandate
{
    use EntityIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Choice(callback={"App\Entity\ElectedRepresentative\MandateTypeEnum", "toArray"})
     */
    #[Groups(['elected_mandate_write', 'elected_mandate_read', 'elected_representative_read', 'elected_representative_list'])]
    private $type;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    #[Groups(['elected_mandate_write', 'elected_mandate_read', 'elected_representative_read'])]
    private $isElected;

    /**
     * @var Zone|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ElectedRepresentative\Zone", cascade={"merge", "detach"})
     *
     * @deprecated Will be replaced by $geoZone
     */
    private $zone;

    /**
     * @var GeoZone|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\Zone")
     *
     * @Assert\Expression(
     *     "value !== null or (value == null and this.getType() === constant('App\\Entity\\ElectedRepresentative\\MandateTypeEnum::EURO_DEPUTY'))",
     *     message="Le périmètre géographique est obligatoire."
     * )
     */
    #[Groups(['elected_mandate_write', 'elected_mandate_read', 'elected_representative_read', 'elected_representative_list'])]
    private $geoZone;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": true})
     */
    #[Groups(['elected_mandate_write', 'elected_mandate_read', 'elected_representative_read'])]
    private $onGoing = true;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date")
     *
     * @Assert\NotBlank
     */
    #[Groups(['elected_mandate_write', 'elected_mandate_read', 'elected_representative_read'])]
    private $beginAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="date", nullable=true)
     *
     * @Assert\Expression(
     *     "value === null or value > this.getBeginAt()",
     *     message="La date de fin du mandat doit être postérieure à la date de début."
     * )
     * @Assert\Expression(
     *     "not (value !== null and this.isOnGoing())",
     *     message="La date de fin ne peut être saisie que dans le cas où le mandat n'est pas en cours."
     * )
     */
    #[Groups(['elected_mandate_write', 'elected_mandate_read', 'elected_representative_read'])]
    private $finishAt;

    /**
     * @var string
     *
     * @ORM\Column(length=10)
     *
     * @Assert\NotBlank
     * @Assert\Choice(callback={"App\Election\VoteListNuanceEnum", "toArray"})
     */
    #[Groups(['elected_mandate_write', 'elected_mandate_read', 'elected_representative_read'])]
    private $politicalAffiliation;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Choice(callback={"App\Entity\ElectedRepresentative\LaREMSupportEnum", "toArray"})
     */
    #[Groups(['elected_mandate_write', 'elected_mandate_read', 'elected_representative_read'])]
    private $laREMSupport;

    /**
     * @var ElectedRepresentative
     *
     * @ORM\ManyToOne(targetEntity="ElectedRepresentative", inversedBy="mandates")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Assert\NotBlank
     */
    #[Groups(['elected_mandate_write', 'elected_mandate_read'])]
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
    #[Groups(['elected_mandate_write', 'elected_mandate_read', 'elected_representative_read'])]
    private $politicalFunctions;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", options={"default": 1})
     */
    private $number = 1;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\Zone")
     */
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
        ?GeoZone $attachedZone = null
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
        return sprintf('%s (%s)', array_search($this->type, MandateTypeEnum::CHOICES), $this->politicalAffiliation);
    }
}
