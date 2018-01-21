<?php

namespace Tests\AppBundle\Command;

use AppBundle\DataFixtures\ORM\LoadTimelineData;
use Tests\AppBundle\SqliteWebTestCase;

/**
 * @group functional
 */
class TimelineSynchronizeCommandTest extends SqliteWebTestCase
{
    public function testCommand()
    {
        $output = $this->runCommand('app:timeline:synchronize');

        $this->assertContains('Synchronizing entity AppBundle\Entity\Timeline\Profile ... done, 5 records indexed', $output);
        $this->assertContains('Synchronizing entity AppBundle\Entity\Timeline\Theme ... done, 5 records indexed', $output);
        $this->assertContains('Synchronizing entity AppBundle\Entity\Timeline\Measure ... done, 17 records indexed', $output);
        $this->assertContains('Timeline has been successfully synchronized with Algolia.', $output);
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
