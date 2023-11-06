<?php

namespace App\Entity\AdherentMessage\Filter;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class MessageFilter extends AbstractUserFilter
{
    use BasicUserFiltersTrait;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private bool $contactOnlyVolunteers = false;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private bool $contactOnlyRunningMates = false;

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
