<?php

namespace Tests\AppBundle\Command;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group command
 */
class TimelineSynchronizeCommandTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testCommand()
    {
        $output = $this->runCommand('app:timeline:synchronize');

        $expectedOutput = <<<EOL
Synchronizing entity AppBundle\Entity\Timeline\Profile ... done, 5 records indexed
Synchronizing entity AppBundle\Entity\Timeline\Theme ... done, 5 records indexed
Synchronizing entity AppBundle\Entity\Timeline\Measure ... done, 17 records indexed
Timeline has been successfully synchronized with Algolia.
EOL;

        $this->assertContains($expectedOutput, $output);
    }
}
