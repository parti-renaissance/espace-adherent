<?php

namespace Tests\App\Command;

use App\Entity\Renaissance\Adhesion\AdherentRequest;
use App\Repository\Renaissance\Adhesion\AdherentRequestRepository;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractCommandTestCase;

#[Group('functional')]
class AdherentRequestCleanupCommandTest extends AbstractCommandTestCase
{
    private ?AdherentRequestRepository $adherentRequestRepository = null;

    public function testCommandSuccess(): void
    {
        self::assertSame(3, $this->countNotCleaned());

        $output = $this->runCommand('app:adherent-request:cleanup', ['days' => 0]);
        $output = $output->getDisplay();

        self::assertStringContainsString('[OK] 3 adherent requests cleaned.', $output);
        self::assertSame(0, $this->countNotCleaned());

        // Ensure second call of same command has nothing left to cleanup
        $output = $this->runCommand('app:adherent-request:cleanup', ['days' => 0]);
        $output = $output->getDisplay();

        self::assertStringContainsString('[OK] 0 adherent requests cleaned.', $output);
    }

    private function countNotCleaned(): int
    {
        return $this->adherentRequestRepository
            ->createQueryBuilder('ar')
            ->select('COUNT(ar.id)')
            ->where('ar.email IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->adherentRequestRepository = $this->getRepository(AdherentRequest::class);
    }

    protected function tearDown(): void
    {
        $this->adherentRequestRepository = null;

        parent::tearDown();
    }
}
