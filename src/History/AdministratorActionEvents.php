<?php

namespace App\History;

class AdministratorActionEvents
{
    public const ADMIN_USER_PROFILE_BEFORE_UPDATE = 'admin.user.profile.before_update';
    public const ADMIN_USER_PROFILE_AFTER_UPDATE = 'admin.user.profile.after_update';
    public const ADMIN_COMMITTEE_BEFORE_UPDATE = 'admin.committee.before_update';
    public const ADMIN_COMMITTEE_AFTER_UPDATE = 'admin.committee.after_update';
    public const ADMIN_COMMITTEE_CREATE = 'admin.committee.create';
    public const ADMIN_COMMITTEE_DELETE = 'admin.committee.delete';
}
