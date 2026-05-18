<?php

declare(strict_types=1);

namespace Tests\App;

abstract class AbstractApiTestCase extends AbstractWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->makeApiClient();
        $this->client->setServerParameter('HTTP_ACCEPT', 'application/json');
    }
}
