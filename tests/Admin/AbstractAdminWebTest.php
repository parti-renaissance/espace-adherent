<?php

namespace Tests\App\Admin;

use Tests\App\AbstractWebCaseTest;

abstract class AbstractAdminWebTest extends AbstractWebCaseTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->makeRenaissanceClient();
    }
}
