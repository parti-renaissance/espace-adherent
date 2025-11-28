<?php

declare(strict_types=1);

namespace Tests\App;

abstract class AbstractEnMarcheWebTestCase extends AbstractWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->makeEMClient();
    }
}
