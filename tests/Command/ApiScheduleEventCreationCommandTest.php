<?php

namespace Tests\App\Command;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group command
 */
class ApiScheduleEventCreationCommandTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testCommand(): void
    {
        $output = $this->runCommand('app:sync:events');

        $this->assertContains('Starting synchronization.', $output);
        $this->assertContains('21/21', $output);
        $this->assertContains('Successfully scheduled for synchronization!', $output);
    }
}
