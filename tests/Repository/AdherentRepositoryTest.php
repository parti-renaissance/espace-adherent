<?php

namespace Tests\AppBundle\Repository;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadCitizenInitiativeCategoryData;
use AppBundle\DataFixtures\ORM\LoadCitizenInitiativeData;
use AppBundle\DataFixtures\ORM\LoadEventCategoryData;
use AppBundle\DataFixtures\ORM\LoadEventData;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenInitiative;
use AppBundle\Entity\Committee;
use AppBundle\Entity\Event;
use AppBundle\Repository\AdherentRepository;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\MysqlWebTestCase;

/**
 * @group functional
 */
class AdherentRepositoryTest extends MysqlWebTestCase
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
        $this->assertSame(14, $this->repository->countActiveAdherents());
    }

    public function testFindAllManagedBy()
    {
        $referent = $this->repository->loadUserByUsername('referent@en-marche-dev.fr');

        $this->assertInstanceOf(Adherent::class, $referent, 'Enabled referent must be returned.');

        $managedByReferent = $this->repository->findAllManagedBy($referent);

        $this->assertCount(7, $managedByReferent, 'Referent should manage 6 adherents + himself in his area.');
        $this->assertSame('Damien SCHMIDT', $managedByReferent[0]->getFullName());
        $this->assertSame('Michel VASSEUR', $managedByReferent[1]->getFullName());
        $this->assertSame('Michelle Dufour', $managedByReferent[2]->getFullName());
        $this->assertSame('Francis Brioul', $managedByReferent[3]->getFullName());
        $this->assertSame('Referent Referent', $managedByReferent[4]->getFullName());
        $this->assertSame('Benjamin Duroc', $managedByReferent[5]->getFullName());
        $this->assertSame('Gisele Berthoux', $managedByReferent[6]->getFullName());
    }

    public function testFindByEvent()
    {
        $event = $this->getEventRepository()->findOneBy(['uuid' => LoadEventData::EVENT_2_UUID]);

        $this->assertInstanceOf(Event::class, $event, 'Event must be returned.');

        $adherents = $this->repository->findByEvent($event);

        $this->assertCount(2, $adherents);
        $this->assertSame('Jacques Picard', $adherents[0]->getFullName());
        $this->assertSame('Francis Brioul', $adherents[1]->getFullName());
    }

    public function testFindNearByCitizenInitiativeInterests()
    {
        $citizenInitiative = $this->getMockBuilder(CitizenInitiative::class)->disableOriginalConstructor()->getMock();
        $citizenInitiative->expects(static::any())->method('getLatitude')->willReturn(48.8713224);
        $citizenInitiative->expects(static::any())->method('getLongitude')->willReturn(2.3353755);
        $citizenInitiative->expects(static::any())->method('getInterests')->willReturn(['jeunesse']);

        $adherents = $this->repository->findNearByCitizenInitiativeInterests($citizenInitiative);

        $this->assertCount(1, $adherents);
        $this->assertSame('Lucie Olivera', $adherents[0]->getFullName());
    }

    public function testFindReferentByCommittee()
    {
        // Foreign Committee with Referent
        $committee = $this->createMock(Committee::class);
        $committee->expects(static::any())->method('getCountry')->willReturn('CH');

        $referent = $this->repository->findReferentByCommittee($committee);

        $this->assertNotNull($referent);
        $this->assertSame('Referent Referent', $referent->getFullName());
        $this->assertSame('referent@en-marche-dev.fr', $referent->getEmailAddress());

        // Committee with no Referent
        $committee = $this->createMock(Committee::class);
        $committee->expects(static::any())->method('getCountry')->willReturn('FR');
        $committee->expects(static::any())->method('getPostalCode')->willReturn('06200');

        $referent = $this->repository->findReferentByCommittee($committee);

        $this->assertNull($referent);

        // Departemental Commitee with Referent
        $committee = $this->createMock(Committee::class);
        $committee->expects(static::any())->method('getCountry')->willReturn('FR');
        $committee->expects(static::any())->method('getPostalCode')->willReturn('77190');

        $referent = $this->repository->findReferentByCommittee($committee);

        $this->assertNotNull($referent);
        $this->assertSame('Referent Referent', $referent->getFullName());
        $this->assertSame('referent@en-marche-dev.fr', $referent->getEmailAddress());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([
            LoadAdherentData::class,
            LoadEventCategoryData::class,
            LoadEventData::class,
            LoadCitizenInitiativeCategoryData::class,
            LoadCitizenInitiativeData::class,
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
