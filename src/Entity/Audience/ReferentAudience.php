<?php

namespace App\Entity\Audience;

use App\Entity\Geo\Zone;
use App\Validator\ManagedZone;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Audience\ReferentAudienceRepository")
 */
class ReferentAudience extends AbstractAudience
{
    /**
     * @var Zone
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\Zone")
     *
     * @Groups({"audience_read", "audience_write"})
     *
     * @ManagedZone(spaceType=App\Geo\ManagedZoneProvider::REFERENT, message="common.zone.not_managed_zone")
     */
    protected $zone;
}
