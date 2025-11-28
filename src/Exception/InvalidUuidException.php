<?php

declare(strict_types=1);

namespace App\Exception;

class InvalidUuidException extends \InvalidArgumentException
{
    use PathAwareExceptionTrait;
}
