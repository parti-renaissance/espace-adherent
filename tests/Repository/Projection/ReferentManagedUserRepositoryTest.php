<?php

namespace Tests\AppBundle\Repository;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadNewsletterSubscriptionData;
use AppBundle\DataFixtures\ORM\LoadReferentManagedUserData;
use AppBundle\Referent\ManagedUsersFilter;
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

    public function testCreateDispatcherIterator()
    {
        $referent = $this->createAdherent('referent@en-marche-dev.fr');
        $referent->setReferent(['77'], '1.123456', '2.34567');

        $results = $this->repository->createDispatcherIterator($referent);

        $expectedEmails = ['francis.brioul@yahoo.com', 'def@en-marche-dev.fr'];

        $count = 0;
        foreach ($results as $key => $result) {
            $this->assertSame($expectedEmails[$key], $result[0]->getEmail());
            ++$count;
        }

        $this->assertSame(2, $count);
    }

    public function testCreateDispatcherIteratorWithOffset()
    {
        $referent = $this->createAdherent('referent@en-marche-dev.fr');
        $referent->setReferent(['77'], '1.123456', '2.34567');

        $filter = $this->createMock(ManagedUsersFilter::class);
        $filter->expects($this->once())->method('getOffset')->willReturn(1);

        $results = $this->repository->createDispatcherIterator($referent, $filter);

        $expectedEmails = ['def@en-marche-dev.fr'];

        $count = 0;
        foreach ($results as $key => $result) {
            $this->assertSame($expectedEmails[$key], $result[0]->getEmail());
            ++$count;
        }

        $this->assertSame(1, $count);
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
