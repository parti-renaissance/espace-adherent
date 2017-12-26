<?php

namespace Tests\AppBundle\Committee\Feed;

use AppBundle\CitizenInitiative\CitizenInitiativeManager;
use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadCitizenInitiativeCategoryData;
use AppBundle\DataFixtures\ORM\LoadCitizenInitiativeData;
use Tests\AppBundle\SqliteWebTestCase;
use Tests\AppBundle\TestHelperTrait;

/**
 * @group functional
 */
class CitizenInitiativeManagerTest extends SqliteWebTestCase
{
    use TestHelperTrait;

    private $manager;

    public function testRemoveOrganizerCitizenInitiatives()
    {
        $organizer = $this->getAdherentRepository()->findByUuid(LoadAdherentData::ADHERENT_3_UUID);
        $this->assertCount(9, $this->getCitizenInitiativeRepository()->findAll());
        $this->assertCount(3, $this->getCitizenInitiativeRepository()->findBy(['organizer' => $organizer]));
        $this->manager->removeOrganizerCitizenInitiatives($organizer);
        $this->assertCount(7, $this->getCitizenInitiativeRepository()->findAll());
        $this->assertCount(0, $this->getCitizenInitiativeRepository()->findBy(['organizer' => $organizer]));
        $this->assertCount(1, $this->getCitizenInitiativeRepository()->findBy(['organizer' => null]));
    }

    public function setUp()
    {
        $this->loadFixtures([
            LoadAdherentData::class,
            LoadCitizenInitiativeCategoryData::class,
            LoadCitizenInitiativeData::class,
        ]);

        $this->container = $this->getContainer();
        $this->manager = $this->get(CitizenInitiativeManager::class);

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
