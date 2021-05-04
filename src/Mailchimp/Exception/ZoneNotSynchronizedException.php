<?php

namespace App\Mailchimp\Exception;

use App\Entity\Geo\Zone;

class ZoneNotSynchronizedException extends \Exception implements SkippableMessageExceptionInterface
{
    private $zone;

    public function __construct(Zone $zone)
    {
        $this->zone = $zone;

        parent::__construct(sprintf('Zone type "%s" is not synchronized with mailchimp.', $zone->getType()));
    }

    public function getZone(): Zone
    {
        return $this->zone;
    }
}
