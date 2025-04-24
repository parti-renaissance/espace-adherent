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
    case TEAM_MEMBER_ADD = 'team_member_add';
    case TEAM_MEMBER_EDIT = 'team_member_edit';
    case TEAM_MEMBER_REMOVE = 'team_member_remove';
}
