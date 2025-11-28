<?php

declare(strict_types=1);

namespace App\OAuth;

use League\OAuth2\Server\Exception\OAuthServerException as BaseOAuthServerException;

class OAuthServerException extends BaseOAuthServerException
{
    private const INVALID_CREDENTIALS_MESSAGE = 'L\'adresse email et le mot de passe que vous avez saisis ne correspondent pas.';
    private const INVALID_REQUEST_MESSAGE = 'Un paramètre requis est manquant, invalide, ou est inclus plus d\'une fois.';
    private const INVALID_REQUEST_HINT_MESSAGE = 'Vérifiez le paramètre \'%s\'.';

    public static function invalidCredentials()
    {
        return new self(self::INVALID_CREDENTIALS_MESSAGE, 6, 'invalid_grant', 400);
    }

    /** {@inheritDoc} */
    public static function invalidRequest($parameter, $hint = null, ?\Throwable $previous = null)
    {
        $hint ??= \sprintf(self::INVALID_REQUEST_HINT_MESSAGE, $parameter);

        return new self(self::INVALID_REQUEST_MESSAGE, 3, 'invalid_request', 400, $hint, null, $previous);
    }
}
