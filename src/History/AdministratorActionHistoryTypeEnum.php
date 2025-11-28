<?php

declare(strict_types=1);

namespace App\History;

enum AdministratorActionHistoryTypeEnum: string
{
    case LOGIN_SUCCESS = 'login_success';
    case LOGIN_FAILURE = 'login_failure';
    case IMPERSONATION_START = 'impersonation_start';
    case IMPERSONATION_END = 'impersonation_end';
    case EXPORT = 'export';
    case ADHERENT_PROFILE_UPDATE = 'adherent_profile_update';
    case ADHERENT_ROLE_ADD = 'adherent_role_add';
    case ADHERENT_ROLE_REMOVE = 'adherent_role_remove';
    case COMMITTEE_UPDATE = 'committee_update';
    case COMMITTEE_CREATE = 'committee_create';
    case COMMITTEE_DELETE = 'committee_delete';
}
