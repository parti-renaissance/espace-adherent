<?php

namespace App\OpenAI\Enum;

enum MessageRoleEnum: string
{
    case USER = 'user';
    case ASSISTANT = 'assistant';
}
