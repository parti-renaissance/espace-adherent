<?php

namespace Tests\App;

abstract class AbstractAppRenaissanceWebTestCase extends AbstractWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->makeAppRenaissanceClient();
    }
}
