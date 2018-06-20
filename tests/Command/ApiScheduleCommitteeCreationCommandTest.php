<?php

namespace Tests\AppBundle\Command;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group command
 */
class ApiScheduleCommitteeCreationCommandTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testCommand(): void
    {
        $output = $this->runCommand('app:sync:committees');

        $this->assertContains('Starting synchronization.', $output);
        $this->assertContains('10/10', $output);
        $this->assertContains('Successfully scheduled for synchronization!', $output);
    }

    public function setUp()
    {
        $this->loadFixtures([
            LoadAdherentData::class,
        ]);

        parent::setUp();
    }

    public function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
