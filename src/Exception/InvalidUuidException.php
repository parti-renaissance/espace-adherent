<?php

namespace App\Exception;

class InvalidUuidException extends \InvalidArgumentException
{
    use PathAwareExceptionTrait;
}
