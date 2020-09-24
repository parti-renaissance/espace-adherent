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

        $this->assertContains('Starting synchronization.', $output);
        $this->assertContains('11/11', $output);
        $this->assertContains('Successfully scheduled for synchronization!', $output);
    }
}
