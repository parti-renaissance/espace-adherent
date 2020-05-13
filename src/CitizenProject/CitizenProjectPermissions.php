<?php

namespace App\CitizenProject;

final class CitizenProjectPermissions
{
    public const SHOW = 'SHOW_CITIZEN_PROJECT';
    public const CREATE = 'CREATE_CITIZEN_PROJECT';
    public const FOLLOW = 'FOLLOW_CITIZEN_PROJECT';
    public const UNFOLLOW = 'UNFOLLOW_CITIZEN_PROJECT';
    public const ADMINISTRATE = 'ADMINISTRATE_CITIZEN_PROJECT';
    public const COMMENT = 'COMMENT_CITIZEN_PROJECT';
    public const SHOW_COMMENT = 'SHOW_COMMENT_CITIZEN_PROJECT';

    public const COMMENTS = [
        self::COMMENT,
        self::SHOW_COMMENT,
    ];
    public const FOLLOWER = [
        self::FOLLOW,
        self::UNFOLLOW,
    ];

    private function __construct()
    {
    }
}
