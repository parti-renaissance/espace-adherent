<?php

namespace App\CitizenAction;

final class CitizenActionPermissions
{
    public const CREATE = 'CREATE_CITIZEN_ACTION';
    public const EDIT = 'EDIT_CITIZEN_ACTION';
    public const CANCEL = 'CANCEL_CITIZEN_ACTION';
    public const REGISTER = 'REGISTER_CITIZEN_ACTION';
    public const UNREGISTER = 'UNREGISTER_CITIZEN_ACTION';

    public const MANAGE = [
        self::CREATE,
        self::EDIT,
        self::CANCEL,
    ];
    public const ATTEND = [
        self::REGISTER,
        self::UNREGISTER,
    ];

    private function __construct()
    {
    }
}
