<?php

namespace AppBundle\Committee;

final class CommitteePermissions
{
    const SHOW = 'SHOW_COMMITTEE';
    const FOLLOW = 'FOLLOW_COMMITTEE';
    const UNFOLLOW = 'UNFOLLOW_COMMITTEE';
    const CREATE = 'CREATE_COMMITTEE';
    const HOST = 'HOST_COMMITTEE';

    private function __construct()
    {
    }
}
