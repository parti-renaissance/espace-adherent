<?php

namespace App\UserListDefinition;

final class UserListDefinitionPermissions
{
    public const ABLE_TO_MANAGE_TYPE = 'ABLE_TO_MANAGE_USER_LIST_DEFINITION_TYPE';
    public const ABLE_TO_MANAGE_MEMBER = 'ABLE_TO_MANAGE_USER_LIST_DEFINITION_MEMBER';

    public const ALL = [
        self::ABLE_TO_MANAGE_TYPE,
        self::ABLE_TO_MANAGE_MEMBER,
    ];

    private function __construct()
    {
    }
}
