<?php

namespace App\Event;

final class EventPermissions
{
    public const HOST = 'HOST_EVENT';
    public const REGISTER = 'REGISTER_EVENT';
    public const UNREGISTER = 'UNREGISTER_EVENT';

    public const ATTEND = [
        self::REGISTER,
        self::UNREGISTER,
    ];

    private function __construct()
    {
    }
}
