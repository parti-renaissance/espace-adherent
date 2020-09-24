<?php

namespace Tests\App\Command;

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
        $this->assertContains('21/21', $output);
        $this->assertContains('Successfully scheduled for synchronization!', $output);
    }
}
