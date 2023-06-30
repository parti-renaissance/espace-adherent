<?php

namespace Tests\App;

abstract class AbstractEnMarcheWebTestCase extends AbstractWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->makeEMClient();
    }
}
