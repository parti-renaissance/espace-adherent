<?php

namespace Tests\AppBundle\Command;

use AppBundle\DataFixtures\ORM\LoadTimelineData;
use Liip\FunctionalTestBundle\Test\WebTestCase;

/**
 * @group functional
 */
class TimelineSynchronizeCommandTest extends WebTestCase
{
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

    public function setUp()
    {
        $this->loadFixtures([
            LoadTimelineData::class,
        ]);

        parent::setUp();
    }

    public function tearDown()
    {
        $this->loadFixtures([]);

        parent::tearDown();
    }
}
