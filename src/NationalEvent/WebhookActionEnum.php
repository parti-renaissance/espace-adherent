<?php

namespace App\NationalEvent;

enum WebhookActionEnum: string
{
    case POST_CREATE = 'POST_CREATE';
    case POST_UPDATE = 'POST_UPDATE';
}
