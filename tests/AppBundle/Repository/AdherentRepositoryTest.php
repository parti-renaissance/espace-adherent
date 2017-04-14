<?php

namespace Tests\AppBundle\Repository;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadEventData;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Event;
use AppBundle\Repository\AdherentRepository;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\SqliteWebTestCase;

class AdherentRepositoryTest extends SqliteWebTestCase
{
    /**
     * @var AdherentRepository
     */
    private $repository;

    use ControllerTestTrait;

    public function testLoadUserByUsername()
    {
        $this->assertInstanceOf(
            Adherent::class,
            $this->repository->loadUserByUsername('carl999@example.fr'),
            'Enabled adherent must be returned.'
        );

        $this->assertNull(
            $this->repository->loadUserByUsername('michelle.dufour@example.ch'),
            'Disabled adherent must not be returned.'
        );

        $this->assertNull(
            $this->repository->loadUserByUsername('someone@foobar.tld'),
            'Non registered adherent must not be returned.'
        );
    }

    public function testCountActiveAdherents()
    {
        $this->assertSame(11, $this->repository->countActiveAdherents());
    }

    public function testFindAllManagedBy()
    {
        $referent = $this->repository->loadUserByUsername('referent@en-marche-dev.fr');

        $this->assertInstanceOf(Adherent::class, $referent, 'Enabled referent must be returned.');

        $managedByReferent = $this->repository->findAllManagedBy($referent);

        $this->assertCount(4, $managedByReferent, 'Referent should manage 3 adherents + himself in his area.');
        $this->assertSame('Michelle Dufour', $managedByReferent[0]->getFullName());
        $this->assertSame('Francis Brioul', $managedByReferent[1]->getFullName());
        $this->assertSame('Referent Referent', $managedByReferent[2]->getFullName());
        $this->assertSame('Gisele Berthoux', $managedByReferent[3]->getFullName());
    }

    public function testFindByEvent()
    {
        $event = $this->getMockBuilder(Event::class)->disableOriginalConstructor()->getMock();
        $event->expects(static::any())->method('getId')->willReturn(2);

        $adherents = $this->repository->findByEvent($event);

        $this->assertCount(2, $adherents);
        $this->assertSame('Jacques Picard', $adherents[0]->getFullName());
        $this->assertSame('Francis Brioul', $adherents[1]->getFullName());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([
            LoadAdherentData::class,
            LoadEventData::class,
        ]);

        $this->container = $this->getContainer();
        $this->repository = $this->getAdherentRepository();
    }

    protected function tearDown()
    {
        $this->loadFixtures([]);

        $this->repository = null;
        $this->container = null;

        parent::tearDown();
    }
}
