<?php

namespace Tests\AppBundle\Committee\Feed;

use AppBundle\DataFixtures\ORM\LoadAdherentSubscriptionData;
use AppBundle\DataFixtures\ORM\LoadAdherentData;
use Tests\AppBundle\SqliteWebTestCase;
use Tests\AppBundle\TestHelperTrait;

/**
 * @group functional
 */
class ActivitySubscriptionManagerTest extends SqliteWebTestCase
{
    use TestHelperTrait;

    private $manager;

    public function testRemoveAdherentActivities()
    {
        $adherent = $this->getAdherentRepository()->findByUuid(LoadAdherentData::ADHERENT_1_UUID);
        $this->assertCount(4, $this->getActivitySubscriptionRepository()->findAll());
        $this->assertCount(2, $this->getActivitySubscriptionRepository()->findBy(['followingAdherent' => $adherent]));
        $this->assertCount(1, $this->getActivitySubscriptionRepository()->findBy(['followedAdherent' => $adherent]));
        $this->manager->removeAdherentActivities($adherent);
        $this->assertCount(1, $this->getActivitySubscriptionRepository()->findAll());
        $this->assertCount(0, $this->getActivitySubscriptionRepository()->findBy(['followingAdherent' => $adherent]));
        $this->assertCount(0, $this->getActivitySubscriptionRepository()->findBy(['followedAdherent' => $adherent]));
    }

    public function setUp()
    {
        $this->loadFixtures([
            LoadAdherentData::class,
            LoadAdherentSubscriptionData::class,
        ]);

        $this->container = $this->getContainer();
        $this->manager = $this->get('app.activity_subscription.manager');

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
