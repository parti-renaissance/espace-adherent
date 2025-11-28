<?php

declare(strict_types=1);

namespace Tests\App\Entity;

use App\Entity\AdherentActivationToken;

class AdherentActivationTokenTest extends AbstractAdherentTokenTestCase
{
    protected $tokenClass = AdherentActivationToken::class;
}
