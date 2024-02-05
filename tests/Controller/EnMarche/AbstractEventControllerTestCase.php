<?php

namespace Tests\App\Controller\EnMarche;

use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

abstract class AbstractEventControllerTestCase extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;
    use RegistrationTrait;

    abstract protected function getEventUrl(): string;
}
