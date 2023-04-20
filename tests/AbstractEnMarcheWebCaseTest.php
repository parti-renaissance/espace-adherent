<?php

namespace Tests\App;

abstract class AbstractEnMarcheWebCaseTest extends AbstractWebCaseTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->makeEMClient();
    }
}
