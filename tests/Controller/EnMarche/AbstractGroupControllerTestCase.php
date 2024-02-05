<?php

namespace Tests\App\Controller\EnMarche;

use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

abstract class AbstractGroupControllerTestCase extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;
    use RegistrationTrait;

    abstract protected function getGroupUrl(): string;
}
