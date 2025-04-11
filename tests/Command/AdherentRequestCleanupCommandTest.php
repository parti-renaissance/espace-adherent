<?php

namespace Tests\App\Command;

use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractCommandTestCase;

#[Group('functional')]
class AdherentRequestCleanupCommandTest extends AbstractCommandTestCase
{
    public function testCommandSuccess(): void
    {
        $output = $this->runCommand('app:adherent-request:cleanup', ['days' => 0]);
        $output = $output->getDisplay();

        self::assertStringContainsString('[OK] 4 adherent requests cleaned.', $output);

        // Ensure second call of same command has nothing left to cleanup
        $output = $this->runCommand('app:adherent-request:cleanup', ['days' => 0]);
        $output = $output->getDisplay();

        self::assertStringContainsString('[OK] 0 adherent requests cleaned.', $output);
    }
}
