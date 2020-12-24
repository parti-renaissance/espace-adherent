<?php

namespace Tests\App\Repository;

use App\Repository\CommitteeRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class CommitteeRepositoryTest extends WebTestCase
{
    /**
     * @var CommitteeRepository
     */
    private $repository;

    use ControllerTestTrait;

    public function testCountApprovedCommittees()
    {
        $this->assertSame(12, $this->repository->countApprovedCommittees());
    }

    public function testFindApprovedCommittees()
    {
        $this->assertCount(12, $this->repository->findApprovedCommittees());
    }

    protected function setUp(): void
    {
        parent::setUp();

        static::$container = $this->getContainer();
        $this->repository = $this->getCommitteeRepository();
    }

    protected function tearDown(): void
    {
        $this->kill();

        $this->repository = null;
        static::$container = null;

        parent::tearDown();
    }
}
