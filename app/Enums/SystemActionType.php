<?php

namespace App\Enums;

enum SystemActionType: string
{
    case USER_REGISTERED = 'user_registered';
    case USER_REGISTERED_VIA_APPLE = 'user_registered_via_apple';
    case USER_REGISTERED_VIA_GOOGLE = 'user_registered_via_google';
    case USER_LOGGED_IN = 'user_logged_in';
    case USER_LOGGED_IN_VIA_APPLE = 'user_logged_in_via_apple';
    case USER_LOGGED_IN_VIA_GOOGLE = 'user_logged_in_via_google';
    case USER_ACCOUNT_DELETED = 'user_account_deleted';
    case REPORT_CREATED = 'report_created';
}
