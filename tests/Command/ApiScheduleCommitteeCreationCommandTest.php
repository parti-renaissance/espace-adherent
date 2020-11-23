<?php

namespace Tests\App\Command;

use Liip\FunctionalTestBundle\Test\WebTestCase;

/**
 * @group command
 */
class ApiScheduleCommitteeCreationCommandTest extends WebTestCase
{
    public function testCommand(): void
    {
        $output = $this->runCommand('app:sync:committees');

        $output = $output->getDisplay();

        $this->assertStringContainsString('Starting synchronization.', $output);
        $this->assertStringContainsString('13/13', $output);
        $this->assertStringContainsString('Successfully scheduled for synchronization!', $output);
    }
}
