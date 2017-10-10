<?php

namespace Tests\AppBundle\Repository;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadNewsletterSubscriptionData;
use AppBundle\DataFixtures\ORM\LoadReferentManagedUserData;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\SqliteWebTestCase;

/**
 * @group functional
 */
class ReferentManagedUserRepositoryTest extends SqliteWebTestCase
{
    /**
     * @var \AppBundle\Repository\Projection\ReferentManagedUserRepository
     */
    private $repository;

    use ControllerTestTrait;

    public function testSearch()
    {
        $referent = $this->createAdherent('referent@en-marche-dev.fr');
        $referent->setReferent(['77'], '1.123456', '2.34567');

        $results = $this->repository->search($referent)->getQuery()->getResult();

        $this->assertCount(2, $results);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSearchWithInvalidReferent()
    {
        $referent = $this->createAdherent('referent@en-marche-dev.fr');
        $referent->setReferent([], '1.123456', '2.34567');

        $this->repository->search($referent);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([
            LoadAdherentData::class,
            LoadNewsletterSubscriptionData::class,
            LoadReferentManagedUserData::class,
        ]);

        $this->container = $this->getContainer();
        $this->repository = $this->getReferentManagedUserRepository();
    }

    protected function tearDown()
    {
        $this->loadFixtures([]);

        $this->repository = null;
        $this->container = null;

        parent::tearDown();
    }
}
