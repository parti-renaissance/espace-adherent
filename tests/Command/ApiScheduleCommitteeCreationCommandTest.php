<?php

namespace Tests\AppBundle\Command;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group command
 */
class ApiScheduleCommitteeCreationCommandTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testCommand(): void
    {
        $output = $this->runCommand('app:sync:committees');

        $this->assertContains('Starting synchronization.', $output);
        $this->assertContains('11/11', $output);
        $this->assertContains('Successfully scheduled for synchronization!', $output);
    }
}
