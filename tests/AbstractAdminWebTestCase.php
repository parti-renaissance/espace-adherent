<?php

namespace Tests\App;

abstract class AbstractAdminWebTestCase extends AbstractWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->makeAdminClient();
    }
}
