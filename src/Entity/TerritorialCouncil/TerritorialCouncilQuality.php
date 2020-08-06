<?php

namespace App\Entity\TerritorialCouncil;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\TerritorialCouncil\Exception\TerritorialCouncilQualityException;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="territorial_council_quality")
 * @ORM\Entity
 *
 * @UniqueEntity(fields={"territorialCouncilMembership", "name"})
 *
 * @Algolia\Index(autoIndex=false)
 */
class TerritorialCouncilQuality
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var TerritorialCouncilMembership|null
     *
     * @ORM\ManyToOne(targetEntity=TerritorialCouncilMembership::class, inversedBy="qualities")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $territorialCouncilMembership;

    /**
     * @ORM\Column(length=255)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $name;

    /**
     * @ORM\Column(length=255)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $zone;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date")
     *
     * @Assert\NotNull
     */
    private $joinedAt;

    public function __construct(string $name, string $zone, \DateTime $joinedAt = null)
    {
        $this->setName($name);
        $this->zone = $zone;
        $this->joinedAt = $joinedAt ?? new \DateTime('now');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTerritorialCouncilMembership(): TerritorialCouncilMembership
    {
        return $this->territorialCouncilMembership;
    }

    public function setTerritorialCouncilMembership(TerritorialCouncilMembership $territorialCouncilMembership): void
    {
        $this->territorialCouncilMembership = $territorialCouncilMembership;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        self::checkQuality($name);
        $this->name = $name;
    }

    public function getJoinedAt(): \DateTime
    {
        return $this->joinedAt;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    private static function checkQuality(string $quality): void
    {
        if (!TerritorialCouncilQualityEnum::isValid($quality)) {
            throw new TerritorialCouncilQualityException(sprintf('Invalid quality for TerritorialCouncil: "%s" given', $quality));
        }
    }
}
