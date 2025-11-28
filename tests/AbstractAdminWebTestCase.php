<?php

declare(strict_types=1);

namespace Tests\App;

abstract class AbstractAdminWebTestCase extends AbstractWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->makeAdminClient();
    }
}
