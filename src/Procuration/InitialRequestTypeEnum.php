<?php

declare(strict_types=1);

namespace App\Procuration;

enum InitialRequestTypeEnum: string
{
    case REQUEST = 'request';
    case PROXY = 'proxy';
}
