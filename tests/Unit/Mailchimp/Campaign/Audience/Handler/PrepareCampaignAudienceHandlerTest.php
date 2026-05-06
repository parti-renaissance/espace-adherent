<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp\Campaign\Audience\Handler;

use App\Doctrine\Utils\BulkInsertHelper;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpStaticSegment;
use App\Mailchimp\Campaign\Audience\AudienceCheckCalculator;
use App\Mailchimp\Campaign\Audience\AudienceCheckEnum;
use App\Mailchimp\Campaign\Audience\BlockReasonEnum;
use App\Mailchimp\Campaign\Audience\Handler\PrepareCampaignAudienceHandler;
use App\Mailchimp\Campaign\Audience\MailchimpParallelPushService;
use App\Mailchimp\Campaign\Audience\Message\PrepareCampaignAudienceMessage;
use App\Mailchimp\Campaign\Audience\PreparationStatusEnum;
use App\Mailchimp\Campaign\Audience\PushResult;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Mailchimp\Campaign\MailchimpStaticSegmentServiceInterface;
use App\Mailchimp\Driver;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class PrepareCampaignAudienceHandlerTest extends TestCase
{
    private const PRE_INITIATED_SEGMENT_ID = 12345;
    private const LIST_ID = 'list-abc';

    public function testHandleCampaignNotFoundLogsAndReturns(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())
            ->method('find')
            ->with(MailchimpCampaign::class, 999)
            ->willReturn(null);
        $em->expects(self::never())->method('flush');

        $handler = $this->createHandler(em: $em);
        $handler(new PrepareCampaignAudienceMessage(999, 'user@example.com'));

        self::assertTrue(true);
    }

    public function testHandleMissingStaticSegmentIdMarksAsFailed(): void
    {
        $campaign = $this->createCampaignWithMessage(); // No segmentId pre-initialised.

        $segmentService = $this->createMock(MailchimpStaticSegmentServiceInterface::class);
        $segmentService->expects(self::never())->method('update');

        $pushService = $this->createMock(MailchimpParallelPushService::class);
        $pushService->expects(self::never())->method('pushEmails');

        $handler = $this->createHandler(
            em: $this->createEmReturning($campaign),
            segmentService: $segmentService,
            pushService: $pushService,
        );

        $handler(new PrepareCampaignAudienceMessage(1, 'user@example.com'));

        self::assertSame(PreparationStatusEnum::Failed, $campaign->getPreparationStatus());
        self::assertSame(BlockReasonEnum::MailchimpUnavailable, $campaign->getBlockReason());
    }

    public function testHandleEmptyAudienceMarksAsFailedWithEmptyReason(): void
    {
        $campaign = $this->createCampaignWithMessage(withSegmentId: true);

        $repo = $this->createRepoReturningEmails([]);

        $segmentService = $this->createMock(MailchimpStaticSegmentServiceInterface::class);
        $segmentService->expects(self::never())->method('update');

        $handler = $this->createHandler(
            em: $this->createEmReturning($campaign),
            repo: $repo,
            segmentService: $segmentService,
        );

        $handler(new PrepareCampaignAudienceMessage(1, 'user@example.com'));

        self::assertSame(PreparationStatusEnum::Failed, $campaign->getPreparationStatus());
        self::assertSame(BlockReasonEnum::Empty, $campaign->getBlockReason());
        self::assertSame(1, $campaign->getMailchimpStaticSegment()->getAttempts());
    }

    public function testHandleTooLargeAudienceMarksAsFailedWithTooLargeReason(): void
    {
        $campaign = $this->createCampaignWithMessage(withSegmentId: true);

        $emails = array_fill(0, 500_001, 'a@b.com');

        $repo = $this->createRepoReturningEmails($emails);

        $segmentService = $this->createMock(MailchimpStaticSegmentServiceInterface::class);
        $segmentService->expects(self::never())->method('update');

        $handler = $this->createHandler(
            em: $this->createEmReturning($campaign),
            repo: $repo,
            segmentService: $segmentService,
        );

        $handler(new PrepareCampaignAudienceMessage(1, 'user@example.com'));

        self::assertSame(BlockReasonEnum::TooLarge, $campaign->getBlockReason());
        self::assertSame(500_001, $campaign->getExpectedAudienceCount());
    }

    public function testHandleResetsSegmentThenPushesEmailsAndFillsStaticSegment(): void
    {
        $campaign = $this->createCampaignWithMessage(withSegmentId: true);
        $emails = $this->generateEmails(15_000);

        $segmentService = $this->createMock(MailchimpStaticSegmentServiceInterface::class);
        $segmentService->expects(self::once())
            ->method('update')
            ->with(self::PRE_INITIATED_SEGMENT_ID, [], self::LIST_ID)
            ->willReturn(true);

        $pushService = $this->createMock(MailchimpParallelPushService::class);
        $pushService->expects(self::once())
            ->method('pushEmails')
            ->with(self::PRE_INITIATED_SEGMENT_ID, self::LIST_ID, $emails, 5, self::isCallable(), self::isCallable())
            ->willReturn(new PushResult(15_000, 0, [], [], 3.2));

        $driver = $this->createStub(Driver::class);
        $driver->method('getSegment')->willReturn(['member_count' => 14_500]);

        $repo = $this->createRepoReturningEmails($emails);
        $repo->method('mapIdsByEmails')->willReturn([]);

        $handler = $this->createHandler(
            em: $this->createEmReturning($campaign),
            segmentService: $segmentService,
            pushService: $pushService,
            driver: $driver,
            repo: $repo,
        );

        $handler(new PrepareCampaignAudienceMessage(1, 'user@example.com'));

        self::assertSame(PreparationStatusEnum::Ready, $campaign->getPreparationStatus());
        self::assertSame(self::PRE_INITIATED_SEGMENT_ID, $campaign->getStaticSegmentId());
        self::assertSame(15_000, $campaign->getExpectedAudienceCount());
        self::assertSame(14_500, $campaign->getPreparedAudienceCount());
        self::assertSame(AudienceCheckEnum::Match, $campaign->getAudienceCheck());

        $segment = $campaign->getMailchimpStaticSegment();
        self::assertNotNull($segment);
        self::assertSame(15_000, $segment->getExpectedCount());
        self::assertSame(15_000, $segment->getPreparedCount());
        self::assertSame(0, $segment->getErroredCount());
        self::assertSame(30, $segment->getChunksTotal()); // 15000 / 500
        self::assertNotNull($segment->getBuiltAt());
        self::assertNotNull($segment->getBuildDurationMs());
        self::assertSame(1, $segment->getAttempts());
        self::assertNull($segment->getErrorSummary());
    }

    public function testHandleStoresErrorSummaryWhenPushHasErrors(): void
    {
        $campaign = $this->createCampaignWithMessage(withSegmentId: true);
        $emails = $this->generateEmails(1_000);

        $segmentService = $this->createStub(MailchimpStaticSegmentServiceInterface::class);
        $segmentService->method('update')->willReturn(true);

        $pushService = $this->createStub(MailchimpParallelPushService::class);
        $pushService->method('pushEmails')->willReturn(
            new PushResult(800, 200, [], ['HTTP 429 on chunk 3', 'HTTP 500 on chunk 5'], 1.5)
        );

        $driver = $this->createStub(Driver::class);
        $driver->method('getSegment')->willReturn(['member_count' => 800]);

        $repo = $this->createRepoReturningEmails($emails);
        $repo->method('mapIdsByEmails')->willReturn([]);

        $handler = $this->createHandler(
            em: $this->createEmReturning($campaign),
            segmentService: $segmentService,
            pushService: $pushService,
            driver: $driver,
            repo: $repo,
        );

        $handler(new PrepareCampaignAudienceMessage(1, 'user@example.com'));

        $segment = $campaign->getMailchimpStaticSegment();
        self::assertSame(200, $segment->getErroredCount());
        self::assertNotNull($segment->getErrorSummary());
        self::assertStringContainsString('HTTP 429', $segment->getErrorSummary());
        self::assertStringContainsString('HTTP 500', $segment->getErrorSummary());
    }

    public function testHandleCapturesFilterSnapshotAndHash(): void
    {
        $campaign = $this->createCampaignWithMessage(withSegmentId: true);

        $filter = new \stdClass();
        $filter->gender = 'male';
        $filter->ageMin = 30;
        $filter->ageMax = 50;

        $message = $campaign->getMessage();
        \assert($message instanceof AdherentMessage);
        // Replace the stub message with one that returns our filter.
        $newMessage = $this->createStub(AdherentMessage::class);
        $newMessage->method('getId')->willReturn(1);
        $newMessage->method('getUuid')->willReturn(Uuid::uuid4());
        $newMessage->method('getFilter')->willReturn($filter);
        $reflection = new \ReflectionObject($campaign);
        $reflection->getProperty('message')->setValue($campaign, $newMessage);

        $segmentService = $this->createStub(MailchimpStaticSegmentServiceInterface::class);
        $segmentService->method('update')->willReturn(true);

        $pushService = $this->createStub(MailchimpParallelPushService::class);
        $pushService->method('pushEmails')->willReturn(new PushResult(1, 0, [], [], 0.1));

        $driver = $this->createStub(Driver::class);
        $driver->method('getSegment')->willReturn(['member_count' => 1]);

        $repo = $this->createRepoReturningEmails(['a@b.com']);
        $repo->method('mapIdsByEmails')->willReturn([]);

        $handler = $this->createHandler(
            em: $this->createEmReturning($campaign),
            segmentService: $segmentService,
            pushService: $pushService,
            driver: $driver,
            repo: $repo,
        );

        $handler(new PrepareCampaignAudienceMessage(1, 'user@example.com'));

        $segment = $campaign->getMailchimpStaticSegment();
        $snapshot = $segment->getFilterSnapshot();
        self::assertIsArray($snapshot);
        self::assertSame('male', $snapshot['gender']);
        self::assertSame(30, $snapshot['ageMin']);
        self::assertSame(50, $snapshot['ageMax']);
        self::assertNotNull($segment->getFilterHash());
        self::assertSame(64, \strlen($segment->getFilterHash())); // sha256 hex
    }

    public function testHandleBulkInsertCalledWithMappedAdherentIds(): void
    {
        $campaign = $this->createCampaignWithMessage(messageId: 42, withSegmentId: true);
        $emails = ['a@b.com', 'c@d.fr', 'e@f.org'];

        $segmentService = $this->createStub(MailchimpStaticSegmentServiceInterface::class);
        $segmentService->method('update')->willReturn(true);

        $pushService = $this->createStub(MailchimpParallelPushService::class);
        $pushService->method('pushEmails')->willReturn(new PushResult(1, 0, [], [], 0.1));

        $driver = $this->createStub(Driver::class);
        $driver->method('getSegment')->willReturn(['member_count' => 3]);

        $repo = $this->createMock(AdherentRepository::class);
        $repo->method('findAdherentEmailsForMessage')->willReturn($emails);
        $repo->expects(self::once())
            ->method('mapIdsByEmails')
            ->with($emails)
            ->willReturn(['a@b.com' => 100, 'c@d.fr' => 200]);

        $bulkInsert = $this->createMock(BulkInsertHelper::class);
        $bulkInsert->expects(self::once())
            ->method('insertIgnore')
            ->with(
                'adherent_message_targeted',
                self::callback(static function (array $rows) {
                    if (3 !== \count($rows)) {
                        return false;
                    }

                    return 100 === $rows[0]['adherent_id']
                        && 200 === $rows[1]['adherent_id']
                        && null === $rows[2]['adherent_id']
                        && 42 === $rows[0]['message_id'];
                }),
            )
            ->willReturn(3);

        $handler = $this->createHandler(
            em: $this->createEmReturning($campaign),
            segmentService: $segmentService,
            pushService: $pushService,
            driver: $driver,
            repo: $repo,
            bulkInsert: $bulkInsert,
        );

        $handler(new PrepareCampaignAudienceMessage(1, 'user@example.com'));
    }

    public function testHandleExceptionDuringResetMarksAsFailedAndRethrows(): void
    {
        $campaign = $this->createCampaignWithMessage(withSegmentId: true);
        $emails = $this->generateEmails(100);

        $segmentService = $this->createStub(MailchimpStaticSegmentServiceInterface::class);
        $segmentService->method('update')->willThrowException(new \RuntimeException('Mailchimp 500'));

        $handler = $this->createHandler(
            em: $this->createEmReturning($campaign),
            repo: $this->createRepoReturningEmails($emails),
            segmentService: $segmentService,
        );

        try {
            $handler(new PrepareCampaignAudienceMessage(1, 'user@example.com'));
            self::fail('Expected RuntimeException');
        } catch (\RuntimeException $e) {
            self::assertSame('Mailchimp 500', $e->getMessage());
        }

        self::assertSame(PreparationStatusEnum::Failed, $campaign->getPreparationStatus());
        self::assertSame(BlockReasonEnum::MailchimpUnavailable, $campaign->getBlockReason());
        self::assertSame('Mailchimp 500', $campaign->getMailchimpStaticSegment()->getErrorSummary());
    }

    private function createHandler(
        ?EntityManagerInterface $em = null,
        ?MailchimpStaticSegmentServiceInterface $segmentService = null,
        ?MailchimpParallelPushService $pushService = null,
        ?Driver $driver = null,
        ?AdherentRepository $repo = null,
        ?BulkInsertHelper $bulkInsert = null,
    ): PrepareCampaignAudienceHandler {
        $mapping = $this->createStub(MailchimpObjectIdMapping::class);
        $mapping->method('getMainListId')->willReturn(self::LIST_ID);

        return new PrepareCampaignAudienceHandler(
            entityManager: $em ?? $this->createStub(EntityManagerInterface::class),
            staticSegmentService: $segmentService ?? $this->createStub(MailchimpStaticSegmentServiceInterface::class),
            parallelPushService: $pushService ?? $this->createStub(MailchimpParallelPushService::class),
            mailchimpObjectIdMapping: $mapping,
            driver: $driver ?? $this->createStub(Driver::class),
            adherentRepository: $repo ?? $this->createStub(AdherentRepository::class),
            bulkInsertHelper: $bulkInsert ?? $this->createStub(BulkInsertHelper::class),
            audienceCheckCalculator: new AudienceCheckCalculator(),
        );
    }

    /**
     * @param list<string> $emails
     */
    private function createRepoReturningEmails(array $emails): AdherentRepository
    {
        $repo = $this->createStub(AdherentRepository::class);
        $repo->method('findAdherentEmailsForMessage')->willReturn($emails);

        return $repo;
    }

    private function createCampaignWithMessage(int $messageId = 1, bool $withSegmentId = false): MailchimpCampaign
    {
        $message = $this->createStub(AdherentMessage::class);
        $message->method('getId')->willReturn($messageId);
        $message->method('getUuid')->willReturn(Uuid::uuid4());

        $campaign = new MailchimpCampaign($message);
        $this->setEntityId($campaign, 1);

        if ($withSegmentId) {
            $campaign->setStaticSegmentId(self::PRE_INITIATED_SEGMENT_ID);
            $campaign->setMailchimpSegmentName('campaign_test');

            $segment = new MailchimpStaticSegment($campaign);
            $segment->setMailchimpSegmentId(self::PRE_INITIATED_SEGMENT_ID);
            $segment->setName('campaign_test');
            $campaign->setMailchimpStaticSegment($segment);
        }

        return $campaign;
    }

    private function setEntityId(object $entity, int $id): void
    {
        $reflection = new \ReflectionObject($entity);
        $reflection->getProperty('id')->setValue($entity, $id);
    }

    /**
     * @return list<string>
     */
    private function generateEmails(int $count): array
    {
        $emails = [];
        for ($i = 0; $i < $count; ++$i) {
            $emails[] = "user{$i}@example.com";
        }

        return $emails;
    }

    private function createEmReturning(MailchimpCampaign $campaign): EntityManagerInterface
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('find')->willReturn($campaign);

        return $em;
    }
}
