<?php

namespace Tests\AppBundle\Committee\Feed;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadEventCategoryData;
use AppBundle\DataFixtures\ORM\LoadEventData;
use Tests\AppBundle\SqliteWebTestCase;
use Tests\AppBundle\TestHelperTrait;

/**
 * @group functional
 */
class EventRegistrationManagerTest extends SqliteWebTestCase
{
    use TestHelperTrait;

    private $manager;

    public function testAnonymizeOrganizerCitizenInitiatives()
    {
        $adherent = $this->getAdherentRepository()->findByUuid(LoadAdherentData::ADHERENT_3_UUID);
        $this->assertCount(15, $this->getEventRegistrationRepository()->findAll());
        $this->assertCount(9, $this->getEventRegistrationRepository()->findBy(['adherentUuid' => $adherent->getUuid()->toString()]));
        $this->manager->anonymizeAdherentRegistrations($adherent);
        $this->assertCount(15, $this->getEventRegistrationRepository()->findAll());
        $this->assertCount(0, $this->getEventRegistrationRepository()->findBy(['adherentUuid' => $adherent->getUuid()->toString()]));
    }

    public function setUp()
    {
        $this->loadFixtures([
            LoadAdherentData::class,
            LoadEventCategoryData::class,
            LoadEventData::class,
        ]);

        $this->container = $this->getContainer();
        $this->manager = $this->get('app.event.registration_manager');

        parent::setUp();
    }

    public function tearDown()
    {
        $this->loadFixtures([]);

        $this->container = null;
        $this->manager = null;

        parent::tearDown();
    }
}
