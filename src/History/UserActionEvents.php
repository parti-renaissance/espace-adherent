<?php

namespace App\History;

class UserActionEvents
{
    public const USER_COMMITTEE_BEFORE_UPDATE = 'user.committee.before_update';
    public const USER_COMMITTEE_AFTER_UPDATE = 'user.committee.after_update';
    public const USER_COMMITTEE_CREATE = 'user.committee.create';
    public const USER_COMMITTEE_DELETE = 'user.committee.delete';
}
