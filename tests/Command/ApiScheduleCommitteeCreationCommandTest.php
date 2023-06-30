<?php

namespace Tests\App\Command;

use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractCommandTestCase;

#[Group('command')]
class ApiScheduleCommitteeCreationCommandTest extends AbstractCommandTestCase
{
    public function testCommand(): void
    {
        $output = $this->runCommand('app:sync:committees');

        $output = $output->getDisplay();

        $this->assertStringContainsString('Starting synchronization.', $output);
        $this->assertStringContainsString('18/18', $output);
        $this->assertStringContainsString('Successfully scheduled for synchronization!', $output);
    }
}
