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
        self::assertCount(4, $this->adherentRequestRepository->findBy(['cleaned' => false]));

        $output = $this->runCommand('app:adherent-request:cleanup', ['days' => 0]);
        $output = $output->getDisplay();

        self::assertStringContainsString('[OK] 4 adherent requests cleaned.', $output);
        self::assertCount(0, $this->adherentRequestRepository->findBy(['cleaned' => false]));

        // Ensure second call of same command has nothing left to cleanup
        $output = $this->runCommand('app:adherent-request:cleanup', ['days' => 0]);
        $output = $output->getDisplay();

        self::assertStringContainsString('[OK] 0 adherent requests cleaned.', $output);
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
