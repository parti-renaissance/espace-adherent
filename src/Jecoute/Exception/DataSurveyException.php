<?php

namespace App\Jecoute\Exception;

use App\Exception\PathAwareExceptionTrait;

class DataSurveyException extends \RuntimeException
{
    use PathAwareExceptionTrait;

    public static function objectNotFound(string $path): self
    {
        return new self('Objet introuvable', 0, null, $path);
    }

    public static function objectInvalid(string $path): self
    {
        return new self('Objet invalide', 0, null, $path);
    }
}
