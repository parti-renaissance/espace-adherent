<?php

namespace Tests\AppBundle\Repository;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadCitizenInitiativeCategoryData;
use AppBundle\DataFixtures\ORM\LoadCitizenInitiativeData;
use AppBundle\Entity\CitizenInitiative;
use AppBundle\Entity\Committee;
use AppBundle\Repository\AdherentRepository;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\MysqlWebTestCase;

/**
 * @group functional
 */
class AdherentRepositoryMysqlTest extends MysqlWebTestCase
{
    /**
     * @var AdherentRepository
     */
    private $repository;

    use ControllerTestTrait;

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

    public function testFindReferentsByCommittee()
    {
        // Foreign Committee with Referent
        $committee = $this->createMock(Committee::class);
        $committee->expects(static::any())->method('getCountry')->willReturn('CH');

        $referents = $this->repository->findReferentsByCommittee($committee);

        $this->assertNotEmpty($referents);
        $this->assertCount(1, $referents);

        $referent = $referents->first();

        $this->assertSame('Referent Referent', $referent->getFullName());
        $this->assertSame('referent@en-marche-dev.fr', $referent->getEmailAddress());

        // Committee with no Referent
        $committee = $this->createMock(Committee::class);
        $committee->expects(static::any())->method('getCountry')->willReturn('FR');
        $committee->expects(static::any())->method('getPostalCode')->willReturn('06200');

        $referents = $this->repository->findReferentsByCommittee($committee);

        $this->assertEmpty($referents);

        // Departemental Commitee with Referent
        $committee = $this->createMock(Committee::class);
        $committee->expects(static::any())->method('getCountry')->willReturn('FR');
        $committee->expects(static::any())->method('getPostalCode')->willReturn('77190');

        $referents = $this->repository->findReferentsByCommittee($committee);

        $this->assertCount(1, $referents);

        $referent = $referents->first();

        $this->assertSame('Referent Referent', $referent->getFullName());
        $this->assertSame('referent@en-marche-dev.fr', $referent->getEmailAddress());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([
            LoadAdherentData::class,
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
