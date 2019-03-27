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

    /**
     * @Event("AppBundle\Procuration\Event\ProcurationProxyEvent")
     */
    public const PROXY_REGISTRATION = 'procuration.proxy_registration';

    /**
     * @Event("AppBundle\Procuration\Event\ProcurationRequestEvent")
     */
    public const REQUEST_REGISTRATION = 'procuration.request_registration';

    private function __construct()
    {
    }
}
