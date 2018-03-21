<?php

namespace Tests\AppBundle\Repository;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Entity\CitizenProject;
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

    public function testFindReferentsByCommittee()
    {
        // Foreign Committee with Referent
        $committee = $this->createMock(Committee::class);
        $committee->expects(static::any())->method('getCountry')->willReturn('CH');

        $referents = $this->repository->findReferentsByCommittee($committee);

        $this->assertNotEmpty($referents);
        $this->assertCount(2, $referents);

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

    public function testFindCoordinatorsByCitizenProject()
    {
        // Foreign Citizen Project with Coordinator
        $citizenProject = $this->createMock(CitizenProject::class);
        $citizenProject->expects(static::any())->method('getCountry')->willReturn('US');

        $coordinators = $this->repository->findCoordinatorsByCitizenProject($citizenProject);

        $this->assertNotEmpty($coordinators);
        $this->assertCount(1, $coordinators);

        $coordinator = $coordinators->first();

        $this->assertSame('Coordinatrice CITIZEN PROJECT', $coordinator->getFullName());
        $this->assertSame('coordinatrice-cp@en-marche-dev.fr', $coordinator->getEmailAddress());

        // Citizen Project with no Coordinator
        $citizenProject = $this->createMock(CitizenProject::class);
        $citizenProject->expects(static::any())->method('getCountry')->willReturn('FR');
        $citizenProject->expects(static::any())->method('getPostalCode')->willReturn('59000');

        $coordinators = $this->repository->findCoordinatorsByCitizenProject($citizenProject);

        $this->assertEmpty($coordinators);

        // Departemental Citizen Project with Coordinator
        $citizenProject = $this->createMock(CitizenProject::class);
        $citizenProject->expects(static::any())->method('getCountry')->willReturn('FR');
        $citizenProject->expects(static::any())->method('getPostalCode')->willReturn('77500');

        $coordinators = $this->repository->findCoordinatorsByCitizenProject($citizenProject);

        $this->assertCount(1, $coordinators);

        $coordinator = $coordinators->first();

        $this->assertSame('Coordinatrice CITIZEN PROJECT', $coordinator->getFullName());
        $this->assertSame('coordinatrice-cp@en-marche-dev.fr', $coordinator->getEmailAddress());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([
            LoadAdherentData::class,
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
