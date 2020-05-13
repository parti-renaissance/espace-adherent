<?php

namespace App\OAuth\Model;

use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

abstract class AbstractGrantToken
{
    use TokenEntityTrait;
    use EntityTrait;
}
