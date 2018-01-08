<?php

namespace AppBundle\Procuration\Event;

final class ProcurationEvents
{
    /**
     * @Event("AppBundle\Procuration\Event\ProcurationRequestEvent")
     */
    public const REQUEST_PROCESSED = 'procuration.request_processed';

    /**
     * @Event("AppBundle\Procuration\Event\ProcurationRequestEvent")
     */
    public const REQUEST_UNPROCESSED = 'procuration.request_unprocessed';

    private function __construct()
    {
    }
}
