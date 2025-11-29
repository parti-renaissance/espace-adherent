<?php

declare(strict_types=1);

namespace Tests\App;

abstract class AbstractApiTestCase extends AbstractRenaissanceWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->client->setServerParameter('HTTP_ACCEPT', 'application/json');
    }
}
