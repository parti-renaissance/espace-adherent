<?php

namespace Tests\App\Command;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group command
 */
class DonationUpdateReferenceCommandTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testCommand(): void
    {
        $output = $this->runCommand('app:donations:update-reference');

        $this->assertContains('Starting Donations reference update.', $output);
        $this->assertContains('Updated 7 Donations reference.', $output);
        $this->assertContains('Donations reference updated successfully!', $output);
    }
}
