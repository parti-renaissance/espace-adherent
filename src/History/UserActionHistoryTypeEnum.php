<?php

namespace App\History;

enum UserActionHistoryTypeEnum: string
{
    case LOGIN_SUCCESS = 'login_success';
    case LOGIN_FAILURE = 'login_failure';
    case PROFILE_UPDATE = 'profile_update';
    case IMPERSONIFICATION_START = 'impersonification_start';
    case IMPERSONIFICATION_END = 'impersonification_end';
    case PASSWORD_RESET_REQUEST = 'password_reset_request';
    case PASSWORD_RESET_VALIDATE = 'password_reset_validate';
    case EMAIL_CHANGE_REQUEST = 'email_change_request';
    case EMAIL_CHANGE_VALIDATE = 'email_change_validate';
}
