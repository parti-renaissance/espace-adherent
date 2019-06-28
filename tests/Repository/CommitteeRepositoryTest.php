<?php

namespace Tests\AppBundle\Repository;

use AppBundle\Repository\CommitteeRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class CommitteeRepositoryTest extends WebTestCase
{
    use ControllerTestTrait;

    /**
     * @var CommitteeRepository
     */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = $this->getContainer();
        $this->repository = $this->getCommitteeRepository();
    }

    protected function tearDown(): void
    {
        $this->kill();

        $this->repository = null;
        $this->container = null;

        parent::tearDown();
    }

    public function testCountApprovedCommittees()
    {
        $this->assertSame(9, $this->repository->countApprovedCommittees());
    }

    public function testFindApprovedCommittees()
    {
        $this->assertCount(9, $this->repository->findApprovedCommittees());
    }
}
