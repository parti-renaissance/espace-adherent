<?php

declare(strict_types=1);

namespace App\Procuration\V2;

enum InitialRequestTypeEnum: string
{
    case REQUEST = 'request';
    case PROXY = 'proxy';
}
