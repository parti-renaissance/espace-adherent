<?php

namespace App\OAuth\Model;

use MyCLabs\Enum\Enum;

/**
 * @method AUTHORIZATION_CODE()
 * @method REFRESH_TOKEN()
 * @method CLIENT_CREDENTIALS()
 * @method PASSWORD()
 * @method IMPLICIT()
 */
final class GrantTypeEnum extends Enum
{
    const AUTHORIZATION_CODE = 'authorization_code';
    const REFRESH_TOKEN = 'refresh_token';
    const CLIENT_CREDENTIALS = 'client_credentials';
    const PASSWORD = 'password';
    const IMPLICIT = 'implicit';

    const GRANT_TYPES_ENABLED = [
        self::AUTHORIZATION_CODE,
        self::REFRESH_TOKEN,
        self::CLIENT_CREDENTIALS,
        self::PASSWORD,
    ];
}
