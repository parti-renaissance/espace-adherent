<?php

declare(strict_types=1);

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
    #[Assert\Length(min: 5, max: 5)]
    #[ORM\Column(length: 10, nullable: true)]
    private $postalCode;

    /**
     * @var Zone
     */
    #[Assert\NotBlank]
    #[ORM\ManyToOne(targetEntity: Zone::class)]
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
