<?php

namespace App\Committee;

final class CommitteePermissions
{
    public const PRE_APPROVE = 'PRE_APPROVE_COMMITTEE';
    public const PRE_REFUSE = 'PRE_REFUSE_COMMITTEE';
    public const SHOW = 'SHOW_COMMITTEE';
    public const FOLLOW = 'FOLLOW_COMMITTEE';
    public const UNFOLLOW = 'UNFOLLOW_COMMITTEE';
    public const CREATE = 'CREATE_COMMITTEE';
    public const HOST = 'HOST_COMMITTEE';
    public const SUPERVISE = 'SUPERVISE_COMMITTEE';
    public const PROMOTE_TO_HOST = 'PROMOTE_TO_HOST_IN_COMMITTEE';
    public const MANAGE_DESIGNATIONS = 'MANAGE_COMMITTEE_DESIGNATIONS';
    public const ADMIN_FEED = 'ADMIN_FEED_COMMITTEE';

    public const FOLLOWER = [
        self::FOLLOW,
        self::UNFOLLOW,
    ];

    private function __construct()
    {
    }
}
