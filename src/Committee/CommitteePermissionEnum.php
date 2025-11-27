<?php

namespace App\Committee;

final class CommitteePermissionEnum
{
    public const SHOW = 'SHOW_COMMITTEE';
    public const FOLLOW = 'FOLLOW_COMMITTEE';
    public const UNFOLLOW = 'UNFOLLOW_COMMITTEE';
    public const HOST = 'HOST_COMMITTEE';
    public const PROMOTE_TO_HOST = 'PROMOTE_TO_HOST_IN_COMMITTEE';
    public const CHANGE_MANDATE = 'CHANGE_MANDATE_OF_COMMITTEE';
    public const ADD_MANDATE = 'ADD_MANDATE_TO_COMMITTEE';
    public const MANAGE_DESIGNATIONS = 'MANAGE_COMMITTEE_DESIGNATIONS';

    public const FOLLOWER = [
        self::FOLLOW,
        self::UNFOLLOW,
    ];

    private function __construct()
    {
    }
}
