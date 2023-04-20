<?php

namespace Tests\App;

abstract class AbstractRenaissanceWebCaseTest extends AbstractWebCaseTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->makeRenaissanceClient();
    }
}
