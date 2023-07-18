<?php

namespace App\Entity\AdherentMandate;

use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AdherentMandate\ElectedRepresentativeAdherentMandateRepository")
 */
class ElectedRepresentativeAdherentMandate extends AbstractAdherentMandate
{
    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Choice(callback={"App\Entity\ElectedRepresentative\MandateTypeEnum", "toArray"})
     */
    public string $mandateType;

    /**
     * @ORM\Column(nullable=true)
     */
    public ?string $delegation = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\Zone")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     *
     * @Assert\Expression(
     *     "value !== null or (value == null and this.getType() === constant('App\\Entity\\ElectedRepresentative\\MandateTypeEnum::EURO_DEPUTY'))",
     *     message="Le périmètre géographique est obligatoire."
     * )
     */
    public ?Zone $zone = null;

    public static function create(
        ?UuidInterface $uuid,
        Adherent $adherent,
        string $mandateType,
        \DateTime $beginAt = null,
        \DateTime $finishAt = null,
        string $delegation = null,
        Zone $zone = null
    ): self {
        $mandate = new self($adherent, null, $beginAt, $finishAt);
        $mandate->uuid = $uuid ?? Uuid::uuid4();
        $mandate->mandateType = $mandateType;
        $mandate->delegation = $delegation;
        $mandate->zone = $zone;

        return $mandate;
    }
}
