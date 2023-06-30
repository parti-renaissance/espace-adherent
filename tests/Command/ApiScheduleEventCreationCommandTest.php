<?php

namespace Tests\App\Command;

use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractCommandTestCase;

#[Group('command')]
class ApiScheduleEventCreationCommandTest extends AbstractCommandTestCase
{
    public function testCommand(): void
    {
        $output = $this->runCommand('app:sync:events');

        $output = $output->getDisplay();

        $this->assertStringContainsString('Starting synchronization.', $output);
        $this->assertStringContainsString('23/23', $output);
        $this->assertStringContainsString('Successfully scheduled for synchronization!', $output);
    }
}
