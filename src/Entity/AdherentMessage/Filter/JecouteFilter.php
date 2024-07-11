<?php

namespace App\Entity\AdherentMessage\Filter;

use App\Entity\Geo\Zone;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class JecouteFilter extends AbstractAdherentMessageFilter implements AdherentSegmentAwareFilterInterface, CampaignAdherentMessageFilterInterface
{
    use AdherentSegmentAwareFilterTrait;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 10, nullable: true)]
    #[Assert\Length(min: 5, max: 5)]
    private $postalCode;

    /**
     * @var Zone
     */
    #[ORM\ManyToOne(targetEntity: Zone::class)]
    #[Assert\NotBlank]
    private $zone;

    public function __construct(?Zone $zone = null)
    {
        parent::__construct();

        $this->zone = $zone;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(Zone $zone): void
    {
        $this->zone = $zone;
    }
}
