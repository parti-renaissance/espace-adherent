<?php

namespace AppBundle\CitizenAction;

final class CitizenActionPermissions
{
    public const CREATE = 'CREATE_CITIZEN_ACTION';
    public const EDIT = 'EDIT_CITIZEN_ACTION';
    public const REGISTER = 'REGISTER_CITIZEN_ACTION';

    public const ORGANIZER_PERMS = [
        self::CREATE,
        self::EDIT,
        self::REGISTER,
    ];

    private function __construct()
    {
    }
}
