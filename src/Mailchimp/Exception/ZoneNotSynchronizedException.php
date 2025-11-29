<?php

declare(strict_types=1);

namespace App\Mailchimp\Exception;

use App\Entity\Geo\Zone;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

class ZoneNotSynchronizedException extends UnrecoverableMessageHandlingException
{
    private $zone;

    public function __construct(Zone $zone)
    {
        $this->zone = $zone;

        parent::__construct(\sprintf('Zone type "%s" is not synchronized with mailchimp.', $zone->getType()));
    }

    public function getZone(): Zone
    {
        return $this->zone;
    }
}
