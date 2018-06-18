<?php

namespace Tests\AppBundle\Command;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadEventData;
use Liip\FunctionalTestBundle\Test\WebTestCase;

/**
 * @group command
 */
class ApiScheduleEventCreationCommandTest extends WebTestCase
{
    public function testCommand(): void
    {
        $output = $this->runCommand('app:sync:events');

        $this->assertContains('Starting synchronization.', $output);
        $this->assertContains('20/20', $output);
        $this->assertContains('Successfully scheduled for synchronization!', $output);
    }

    public function setUp()
    {
        $this->loadFixtures([
            LoadAdherentData::class,
            LoadEventData::class,
        ]);

        parent::setUp();
    }

    public function tearDown()
    {
        $this->loadFixtures([]);

        parent::tearDown();
    }
}
