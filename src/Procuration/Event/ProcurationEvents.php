<?php

namespace App\Procuration\Event;

final class ProcurationEvents
{
    /**
     * @Event("App\Procuration\Event\ProcurationRequestEvent")
     */
    public const REQUEST_PROCESSED = 'procuration.request_processed';

    /**
     * @Event("App\Procuration\Event\ProcurationRequestEvent")
     */
    public const REQUEST_UNPROCESSED = 'procuration.request_unprocessed';

    /**
     * @Event("App\Procuration\Event\ProcurationProxyEvent")
     */
    public const PROXY_REGISTRATION = 'procuration.proxy_registration';

    /**
     * @Event("App\Procuration\Event\ProcurationRequestEvent")
     */
    public const REQUEST_REGISTRATION = 'procuration.request_registration';

    private function __construct()
    {
    }
}
