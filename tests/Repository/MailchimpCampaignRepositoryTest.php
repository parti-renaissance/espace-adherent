<?php

declare(strict_types=1);

namespace Tests\App\Repository;

use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Repository\MailchimpCampaignRepository;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractKernelTestCase;

#[Group('functional')]
class MailchimpCampaignRepositoryTest extends AbstractKernelTestCase
{
    private ?MailchimpCampaignRepository $repository = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->getRepository(MailchimpCampaign::class);
    }

    protected function tearDown(): void
    {
        $this->repository = null;

        parent::tearDown();
    }

    public function testTryClaimRecoveryClaimsOnceThenRefusesConcurrentClaims(): void
    {
        $campaign = $this->persistFreshCampaign();

        self::assertNull($campaign->getRecoveryAttemptedAt(), 'A fresh campaign has no recovery claim yet.');

        // First worker wins the atomic claim (UPDATE ... WHERE recovery_attempted_at IS NULL → 1 row).
        self::assertTrue($this->repository->tryClaimRecovery($campaign->getId()));

        // The bulk UPDATE bypasses the unit of work; refresh to observe the persisted claim.
        $this->manager->refresh($campaign);
        self::assertNotNull($campaign->getRecoveryAttemptedAt(), 'The claim must persist recovery_attempted_at.');

        // A second (concurrent) claim on the same campaign matches 0 rows → refused. Single recovery.
        self::assertFalse($this->repository->tryClaimRecovery($campaign->getId()));
    }

    public function testTryClaimRecoveryReturnsFalseForUnknownCampaign(): void
    {
        self::assertFalse($this->repository->tryClaimRecovery(\PHP_INT_MAX));
    }

    private function persistFreshCampaign(): MailchimpCampaign
    {
        $message = $this->getRepository(AdherentMessage::class)->findOneBy([]);
        self::assertInstanceOf(AdherentMessage::class, $message, 'Fixtures must provide at least one AdherentMessage.');

        $campaign = new MailchimpCampaign($message);
        $message->addMailchimpCampaign($campaign);

        $this->manager->persist($campaign);
        $this->manager->flush();

        return $campaign;
    }
}
