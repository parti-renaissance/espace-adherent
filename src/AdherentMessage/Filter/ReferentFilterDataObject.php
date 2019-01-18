<?php

namespace AppBundle\AdherentMessage\Filter;

use Symfony\Component\Validator\Constraints as Assert;

class ReferentFilterDataObject implements FilterDataObjectInterface
{
    /**
     * @Assert\Count(min=1)
     */
    private $zones;

    public function __construct(array $zones = [])
    {
        $this->zones = $zones;
    }

    public function getZones(): array
    {
        return $this->zones;
    }

    public function setZones(array $zones): void
    {
        $this->zones = $zones;
    }

    public function serialize()
    {
        return serialize([$this->zones]);
    }

    public function unserialize($serialized)
    {
        list($this->zones) = unserialize($serialized);
    }
}
