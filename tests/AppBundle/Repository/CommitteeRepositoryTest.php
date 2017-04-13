<?php

namespace Tests\AppBundle\Repository;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Repository\CommitteeRepository;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\SqliteWebTestCase;

class CommitteeRepositoryTest extends SqliteWebTestCase
{
    /**
     * @var CommitteeRepository
     */
    private $repository;

    use ControllerTestTrait;

    public function testCountApprovedCommittees()
    {
        $this->assertSame(8, $this->repository->countApprovedCommittees());
    }

    public function testFindApprovedCommittees()
    {
        $this->assertCount(8, $this->repository->findApprovedCommittees());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([
            LoadAdherentData::class,
        ]);

        $this->container = $this->getContainer();
        $this->repository = $this->getCommitteeRepository();
    }

    protected function tearDown()
    {
        $this->loadFixtures([]);

        $this->repository = null;
        $this->container = null;

        parent::tearDown();
    }
}
