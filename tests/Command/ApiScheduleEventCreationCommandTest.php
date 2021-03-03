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

        $output = $output->getDisplay();

        $this->assertStringContainsString('Starting synchronization.', $output);
        $this->assertStringContainsString('22/22', $output);
        $this->assertStringContainsString('Successfully scheduled for synchronization!', $output);
    }
}
