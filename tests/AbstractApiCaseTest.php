<?php

namespace Tests\App;

abstract class AbstractApiCaseTest extends AbstractEnMarcheWebCaseTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->client->setServerParameter('HTTP_ACCEPT', 'application/json');
    }
}
