<?php

namespace App\Entity\TerritorialCouncil;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\TerritorialCouncil\Exception\TerritorialCouncilQualityException;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table
 * @ORM\Entity
 *
 * @UniqueEntity(fields={"adherent", "territorialCouncil"})
 *
 * @Algolia\Index(autoIndex=false)
 */
class TerritorialCouncilMembership
{
    use EntityIdentityTrait;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Adherent", inversedBy="territorialCouncilMembership")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE", unique=true)
     */
    private $adherent;

    /**
     * @var TerritorialCouncil|null
     *
     * @ORM\ManyToOne(targetEntity=TerritorialCouncil::class, inversedBy="memberships", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $territorialCouncil;

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array")
     */
    private $qualities;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date")
     *
     * @Assert\NotNull
     */
    private $joinedAt;

    public function __construct(
        TerritorialCouncil $territorialCouncil,
        Adherent $adherent,
        string $quality,
        \DateTime $joinedAt = null
    ) {
        $this->uuid = Uuid::uuid4();
        $this->territorialCouncil = $territorialCouncil;
        $this->adherent = $adherent;
        $this->qualities = [$quality];
        $this->joinedAt = $joinedAt ?? new \DateTime();
    }

    public function getTerritorialCouncil(): TerritorialCouncil
    {
        return $this->territorialCouncil;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }

    public function getQualities(): array
    {
        return $this->qualities;
    }

    public function addQuality(string $quality): void
    {
        if (!\in_array($quality, $this->qualities, true)) {
            self::checkQuality($quality);
            $this->qualities[] = $quality;
        }
    }

    /**
     * @param string[] $qualities
     */
    public function setQualities(array $qualities): void
    {
        \array_walk($qualities, function (string $quality) {
            self::checkQuality($quality);
        });
        $this->qualities = $qualities;
    }

    public function getJoinedAt(): \DateTime
    {
        return $this->joinedAt;
    }

    private static function checkQuality(string $quality): void
    {
        if (!TerritorialCouncilQualityEnum::isValid($quality)) {
            throw new TerritorialCouncilQualityException(sprintf('Invalid quality for TerritorialCouncil: "%s" given', $quality));
        }
    }
}
