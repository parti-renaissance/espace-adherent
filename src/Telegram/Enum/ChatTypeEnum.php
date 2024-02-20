<?php

namespace App\Telegram\Enum;

enum ChatTypeEnum: string
{
    case PRIVATE = 'private';
    case GROUP = 'group';
}
