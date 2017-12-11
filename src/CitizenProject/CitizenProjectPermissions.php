<?php

namespace AppBundle\CitizenProject;

final class CitizenProjectPermissions
{
    const SHOW = 'SHOW_CITIZEN_PROJECT';
    const FOLLOW = 'FOLLOW_CITIZEN_PROJECT';
    const UNFOLLOW = 'UNFOLLOW_CITIZEN_PROJECT';
    const ADMINISTRATE = 'ADMINISTRATE_CITIZEN_PROJECT';
    const COMMENT = 'COMMENT_CITIZEN_PROJECT';
    const SHOW_COMMENT = 'SHOW_COMMENT_CITIZEN_PROJECT';

    private function __construct()
    {
    }
}
