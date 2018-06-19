<?php

namespace Tests\AppBundle\Command;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadDonationData;
use Liip\FunctionalTestBundle\Test\WebTestCase;

/**
 * @group command
 */
class DonationUpdateReferenceCommandTest extends WebTestCase
{
    public function testCommand(): void
    {
        $output = $this->runCommand('app:donations:update-reference');

        $this->assertContains('Starting Donations reference update..', $output);
        $this->assertContains('Updated 7 Donations reference.', $output);
        $this->assertContains('Donations reference updated successfully!', $output);
    }

    public function setUp()
    {
        $this->loadFixtures([
            LoadAdherentData::class,
            LoadDonationData::class,
        ]);

        parent::setUp();
    }

    public function tearDown()
    {
        $this->loadFixtures([]);

        parent::tearDown();
    }
}
