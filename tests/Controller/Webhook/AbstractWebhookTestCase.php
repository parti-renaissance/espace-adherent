<?php

namespace Tests\App\Controller\Webhook;

use Tests\App\AbstractWebTestCase;

abstract class AbstractWebhookTestCase extends AbstractWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->makeWebhookClient();
    }
}
