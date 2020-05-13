<?php

namespace App\Committee;

final class CommitteePermissions
{
    public const SHOW = 'SHOW_COMMITTEE';
    public const FOLLOW = 'FOLLOW_COMMITTEE';
    public const UNFOLLOW = 'UNFOLLOW_COMMITTEE';
    public const CREATE = 'CREATE_COMMITTEE';
    public const HOST = 'HOST_COMMITTEE';
    public const SUPERVISE = 'SUPERVISE_COMMITTEE';
    public const ADMIN_FEED = 'ADMIN_FEED_COMMITTEE';

    public const FOLLOWER = [
        self::FOLLOW,
        self::UNFOLLOW,
    ];

    private function __construct()
    {
    }
}
