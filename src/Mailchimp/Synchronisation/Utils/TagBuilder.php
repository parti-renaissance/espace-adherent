<?php

namespace App\Mailchimp\Synchronisation\Utils;

abstract class TagBuilder
{
    public static function createCauseFollowTag(int $causeId): string
    {
        return 'cause_'.$causeId;
    }
}
