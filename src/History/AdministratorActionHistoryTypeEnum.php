<?php

namespace App\History;

enum AdministratorActionHistoryTypeEnum: string
{
    case LOGIN_SUCCESS = 'login_success';
    case LOGIN_FAILURE = 'login_failure';
    case IMPERSONATION_START = 'impersonation_start';
    case IMPERSONATION_END = 'impersonation_end';
    case EXPORT = 'export';
    case ADHERENT_PROFILE_UPDATE = 'adherent_profile_update';
}
