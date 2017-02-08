<?php

namespace Tests\AppBundle\Repository;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadNewsletterSubscriptionData;
use AppBundle\Repository\NewsletterSubscriptionRepository;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\SqliteWebTestCase;

class NewsletterSubscriptionRepositoryTest extends SqliteWebTestCase
{
    /** @var NewsletterSubscriptionRepository */
    private $repository;

    use ControllerTestTrait;

    public function testFindAllManagedBy()
    {
        $referent = $this->getAdherentRepository()->loadUserByUsername('referent@en-marche-dev.fr');

        $managedByReferent = $this->repository->findAllManagedBy($referent);

        $this->assertCount(2, $managedByReferent, 'Referent should manage 2 newsletter subscribers in his area.');
        $this->assertSame('abc@en-marche-dev.fr', $managedByReferent[0]->getEmail());
        $this->assertSame('def@en-marche-dev.fr', $managedByReferent[1]->getEmail());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([
            LoadAdherentData::class,
            LoadNewsletterSubscriptionData::class,
        ]);

        $this->container = $this->getContainer();
        $this->repository = $this->getNewsletterSubscriptionRepository();
    }

    protected function tearDown()
    {
        $this->loadFixtures([]);

        $this->repository = null;
        $this->container = null;

        parent::tearDown();
    }
}
