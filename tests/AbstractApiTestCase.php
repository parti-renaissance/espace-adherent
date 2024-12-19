<?php

namespace Tests\App;

abstract class AbstractApiTestCase extends AbstractRenaissanceWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->client->setServerParameter('HTTP_ACCEPT', 'application/json');
        //        $this->client->setServerParameter('HTTP_CONTENT_TYPE', 'application/json');
        //        $this->client->setServerParameter('HTTP_CONTENT-TYPE', 'application/json');
    }
}
