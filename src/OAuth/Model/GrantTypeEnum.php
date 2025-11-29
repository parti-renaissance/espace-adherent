<?php

declare(strict_types=1);

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
    public const AUTHORIZATION_CODE = 'authorization_code';
    public const REFRESH_TOKEN = 'refresh_token';
    public const CLIENT_CREDENTIALS = 'client_credentials';
    public const PASSWORD = 'password';
    public const IMPLICIT = 'implicit';

    public const GRANT_TYPES_ENABLED = [
        self::AUTHORIZATION_CODE,
        self::REFRESH_TOKEN,
        self::CLIENT_CREDENTIALS,
        self::PASSWORD,
    ];
}
