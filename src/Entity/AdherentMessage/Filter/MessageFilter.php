<?php

namespace App\Entity\AdherentMessage\Filter;

use App\Collection\ZoneCollection;
use App\Entity\EntityZoneTrait;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class MessageFilter extends AbstractUserFilter
{
    use BasicUserFiltersTrait;
    use EntityZoneTrait;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Geo\Zone", cascade={"persist"})
     * @ORM\JoinTable(name="adherent_message_filter_zone")
     */
    protected Collection $zones;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private bool $contactOnlyVolunteers = false;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private bool $contactOnlyRunningMates = false;

    public function __construct(array $zones = [])
    {
        $this->zones = new ZoneCollection($zones);
    }

    public function getContactOnlyVolunteers(): bool
    {
        return $this->contactOnlyVolunteers;
    }

    public function setContactOnlyVolunteers(bool $contactOnlyVolunteers): void
    {
        $this->contactOnlyVolunteers = $contactOnlyVolunteers;
    }

    public function getContactOnlyRunningMates(): bool
    {
        return $this->contactOnlyRunningMates;
    }

    public function setContactOnlyRunningMates(bool $contactOnlyRunningMates): void
    {
        $this->contactOnlyRunningMates = $contactOnlyRunningMates;
    }
}
