<?php

namespace Tests\App;

abstract class AbstractRenaissanceWebTestCase extends AbstractWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->makeRenaissanceClient();
    }
}
