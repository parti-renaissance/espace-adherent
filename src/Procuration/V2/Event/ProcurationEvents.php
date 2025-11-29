<?php

declare(strict_types=1);

namespace App\Procuration\V2\Event;

class ProcurationEvents
{
    public const PROXY_CREATED = 'procuration.proxy.created';
    public const PROXY_BEFORE_UPDATE = 'procuration.proxy.before_update';
    public const PROXY_AFTER_UPDATE = 'procuration.proxy.after_update';

    public const REQUEST_CREATED = 'procuration.request.created';
    public const REQUEST_BEFORE_UPDATE = 'procuration.request.before_update';
    public const REQUEST_AFTER_UPDATE = 'procuration.request.after_update';
}
