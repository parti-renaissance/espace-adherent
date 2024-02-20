<?php

namespace Tests\App;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractWebTestCase extends WebTestCase
{
    /** @var KernelBrowser */
    protected $client;
    /** @var EntityManagerInterface */
    protected $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->client->setServerParameter('HTTP_ACCEPT', 'text/html');

        $this->manager = static::getContainer()->get(EntityManagerInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        static::ensureKernelShutdown();
        static::$kernel = null;
        static::$booted = false;

        $this->client = null;
        $this->manager = null;
    }

    protected function makeEMClient(): void
    {
        $this->client->setServerParameter('HTTP_HOST', static::getContainer()->getParameter('app_host'));
    }

    protected function makeRenaissanceClient(): void
    {
        $this->client->setServerParameter('HTTP_HOST', static::getContainer()->getParameter('app_renaissance_host'));
    }

    protected function makeWebhookClient(): void
    {
        $this->client->setServerParameter('HTTP_HOST', static::getContainer()->getParameter('webhook_renaissance_host'));
    }
}
