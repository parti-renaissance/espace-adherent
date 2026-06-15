<?php

declare(strict_types=1);

namespace Tests\App\Repository\AdherentMessage;

use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MandrillFallbackChunk;
use App\Mailchimp\Campaign\Fallback\MandrillFallbackChunkStatusEnum;
use App\Repository\AdherentMessage\MandrillFallbackChunkRepository;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractKernelTestCase;

#[Group('functional')]
class MandrillFallbackChunkRepositoryTest extends AbstractKernelTestCase
{
    private ?MandrillFallbackChunkRepository $repository = null;

    public function testClaimForSendingClaimsPendingChunk(): void
    {
        $campaign = $this->createChunk(1, MandrillFallbackChunkStatusEnum::Pending);

        self::assertTrue($this->repository->claimForSending($campaign->getId(), 1));
        self::assertSame(MandrillFallbackChunkStatusEnum::Sending, $this->repository->findStatus($campaign->getId(), 1));
    }

    public function testClaimForSendingReturnsFalseWhenAlreadySending(): void
    {
        $campaign = $this->createChunk(2, MandrillFallbackChunkStatusEnum::Sending);

        self::assertFalse($this->repository->claimForSending($campaign->getId(), 2));
        self::assertSame(MandrillFallbackChunkStatusEnum::Sending, $this->repository->findStatus($campaign->getId(), 2));
    }

    public function testClaimForSendingReturnsFalseWhenAlreadySent(): void
    {
        $campaign = $this->createChunk(3, MandrillFallbackChunkStatusEnum::Sent);

        self::assertFalse($this->repository->claimForSending($campaign->getId(), 3));
        self::assertSame(MandrillFallbackChunkStatusEnum::Sent, $this->repository->findStatus($campaign->getId(), 3));
    }

    public function testMarkSentSetsStatusAndTimestamp(): void
    {
        $campaign = $this->createChunk(4, MandrillFallbackChunkStatusEnum::Sending);

        $this->repository->markSent($campaign->getId(), 4);

        self::assertSame(MandrillFallbackChunkStatusEnum::Sent, $this->repository->findStatus($campaign->getId(), 4));
        $this->manager->clear();
        $row = $this->repository->findOneBy(['campaign' => $campaign, 'chunkNumber' => 4]);
        self::assertNotNull($row->sentAt);
    }

    public function testMarkNeedsReviewSetsStatus(): void
    {
        $campaign = $this->createChunk(5, MandrillFallbackChunkStatusEnum::Sending);

        $this->repository->markNeedsReview($campaign->getId(), 5);

        self::assertSame(MandrillFallbackChunkStatusEnum::NeedsReview, $this->repository->findStatus($campaign->getId(), 5));
    }

    public function testFindStatusReturnsNullForUnknownChunk(): void
    {
        $campaign = $this->getCampaign();

        self::assertNull($this->repository->findStatus($campaign->getId(), 999));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->getRepository(MandrillFallbackChunk::class);
    }

    protected function tearDown(): void
    {
        $this->repository = null;

        parent::tearDown();
    }

    private function createChunk(int $chunkNumber, MandrillFallbackChunkStatusEnum $status): MailchimpCampaign
    {
        $campaign = $this->getCampaign();

        $chunk = new MandrillFallbackChunk($campaign, $chunkNumber);
        $chunk->status = $status;

        $this->manager->persist($chunk);
        $this->manager->flush();
        $this->manager->clear();

        return $campaign;
    }

    private function getCampaign(): MailchimpCampaign
    {
        $campaign = $this->getRepository(MailchimpCampaign::class)->findOneBy([]);
        self::assertInstanceOf(MailchimpCampaign::class, $campaign, 'A fixture MailchimpCampaign is required.');

        return $campaign;
    }
}
