<?php

namespace App\Chatbot\Enum;

enum MessageRoleEnum: string
{
    case USER = 'user';
    case ASSISTANT = 'assistant';
}
