<?php

namespace Tests\App\Command;

use Liip\FunctionalTestBundle\Test\WebTestCase;

/**
 * @group command
 */
class DonationUpdateReferenceCommandTest extends WebTestCase
{
    public function testCommand(): void
    {
        $output = $this->runCommand('app:donations:update-reference');

        $this->assertContains('Starting Donations reference update.', $output);
        $this->assertContains('Updated 7 Donations reference.', $output);
        $this->assertContains('Donations reference updated successfully!', $output);
    }
}
