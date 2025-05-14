<?php

namespace App\History;

enum UserActionHistoryTypeEnum: string
{
    case LOGIN_SUCCESS = 'login_success';
    case LOGIN_FAILURE = 'login_failure';
    case PROFILE_UPDATE = 'profile_update';
    case IMPERSONATION_START = 'impersonation_start';
    case IMPERSONATION_END = 'impersonation_end';
    case PASSWORD_RESET_REQUEST = 'password_reset_request';
    case PASSWORD_RESET_VALIDATE = 'password_reset_validate';
    case EMAIL_CHANGE_REQUEST = 'email_change_request';
    case EMAIL_CHANGE_VALIDATE = 'email_change_validate';
    case ROLE_ADD = 'role_add';
    case ROLE_REMOVE = 'role_remove';
    case LIVE_VIEW = 'live_view';
    case DELEGATED_ACCESS_ADD = 'delegated_access_add';
    case DELEGATED_ACCESS_EDIT = 'delegated_access_edit';
    case DELEGATED_ACCESS_REMOVE = 'delegated_access_remove';
    case AGORA_MEMBERSHIP_ADD = 'agora_membership_add';
    case AGORA_MEMBERSHIP_REMOVE = 'agora_membership_remove';
    case AGORA_PRESIDENT_ADD = 'agora_president_add';
    case AGORA_PRESIDENT_REMOVE = 'agora_president_remove';
    case AGORA_GENERAL_SECRETARY_ADD = 'agora_general_secretary_add';
    case AGORA_GENERAL_SECRETARY_REMOVE = 'agora_general_secretary_remove';
}
