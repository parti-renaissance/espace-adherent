<?php

namespace Tests\App\Repository;

use App\Repository\CommitteeRepository;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractKernelTestCase;

#[Group('functional')]
class CommitteeRepositoryTest extends AbstractKernelTestCase
{
    /**
     * @var CommitteeRepository
     */
    private $repository;

    public function testFindApprovedCommittees()
    {
        $this->assertCount(16, $this->repository->findApprovedCommittees());
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
