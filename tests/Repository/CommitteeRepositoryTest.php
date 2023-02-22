<?php

namespace Tests\App\Repository;

use App\Repository\CommitteeRepository;
use Tests\App\AbstractKernelTestCase;

/**
 * @group functional
 */
class CommitteeRepositoryTest extends AbstractKernelTestCase
{
    /**
     * @var CommitteeRepository
     */
    private $repository;

    public function testCountApprovedCommittees()
    {
        $this->assertSame(14, $this->repository->countApprovedCommittees());
    }

    public function testFindApprovedCommittees()
    {
        $this->assertCount(14, $this->repository->findApprovedCommittees());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->getCommitteeRepository();
    }

    protected function tearDown(): void
    {
        $this->repository = null;

        parent::tearDown();
    }
}
