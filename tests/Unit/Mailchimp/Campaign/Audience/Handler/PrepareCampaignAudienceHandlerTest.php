<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp\Campaign\Audience\Handler;

use App\Doctrine\Utils\BulkInsertHelper;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpStaticSegment;
use App\Mailchimp\Campaign\Audience\BlockReasonEnum;
use App\Mailchimp\Campaign\Audience\Handler\PrepareCampaignAudienceHandler;
use App\Mailchimp\Campaign\Audience\Message\FinalizeCampaignAudienceMessage;
use App\Mailchimp\Campaign\Audience\Message\PrepareCampaignAudienceMessage;
use App\Mailchimp\Campaign\Audience\Message\ProcessAudienceChunkMessage;
use App\Mailchimp\Campaign\Audience\PreparationStatusEnum;
use App\Mailchimp\Campaign\Audience\SegmentMemberStatusEnum;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Mailchimp\Campaign\MailchimpStaticSegmentServiceInterface;
use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PrepareCampaignAudienceHandlerTest extends TestCase
{
    public function testCampaignNotFoundLogsAndReturns(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('find')->willReturn(null);
        $em->expects(self::never())->method('flush');

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $handler = $this->buildHandler($em, bus: $bus);
        $handler(new PrepareCampaignAudienceMessage(99, 1));
    }

    public function testAlreadyReadyWithoutPendingSendIsNoOp(): void
    {
        $message = new AdherentMessage();
        $campaign = $this->buildCampaign($message, segmentId: 555);
        $campaign->markAsPreparing($this->createStub(Adherent::class));
        $campaign->markAsReady();
        // pendingSend not set → guard returns early.

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('find')->willReturn($campaign);
        $em->expects(self::never())->method('flush');

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $handler = $this->buildHandler($em, bus: $bus);
        $handler(new PrepareCampaignAudienceMessage(7, 1));
    }

    public function testPendingSendBypassesFreshnessShortCircuit(): void
    {
        // No filter on the message → captureFilterSnapshot is a no-op,
        // so we don't need to mock the normalizer.
        $message = new AdherentMessage();
        $this->setEntityId($message, 100);
        $campaign = $this->buildCampaign($message, segmentId: 555);
        $campaign->markAsPreparing($this->createStub(Adherent::class));
        $campaign->markAsReady();
        $campaign->markAsPendingSend();

        $adherentRepository = $this->createStub(AdherentRepository::class);
        $adherentRepository->method('findAdherentIdsForMessage')->willReturn([]);

        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('find')->willReturnCallback($this->buildFindCallback($campaign));

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('error')
            ->with(
                '[AudiencePrepare] Auto-send aborted: preparation failed',
                self::callback(function (array $context): bool {
                    return 7 === $context['campaign_id']
                        && BlockReasonEnum::Empty->value === $context['block_reason'];
                }),
            )
        ;

        $handler = $this->buildHandler($em, adherentRepository: $adherentRepository, bus: $bus, logger: $logger);
        $handler(new PrepareCampaignAudienceMessage(7, 1));

        // Got past the early return: status moves out of Ready (rebuild attempted).
        self::assertSame(PreparationStatusEnum::Failed, $campaign->getPreparationStatus());
        self::assertFalse($campaign->isPendingSend(), 'markAsFailed clears the flag so next /send re-arms preparation');
    }

    public function testMissingStaticSegmentMarksAsFailed(): void
    {
        $message = new AdherentMessage();
        $campaign = new MailchimpCampaign($message);
        $this->setEntityId($campaign, 7);
        $message->addMailchimpCampaign($campaign);
        // No setMailchimpStaticSegment, no setStaticSegmentId.

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('find')->willReturn($campaign);
        $em->expects(self::once())->method('flush');

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $handler = $this->buildHandler($em, bus: $bus);
        $handler(new PrepareCampaignAudienceMessage(7, 1));

        self::assertSame(PreparationStatusEnum::Failed, $campaign->getPreparationStatus());
        self::assertSame(BlockReasonEnum::MailchimpUnavailable, $campaign->getBlockReason());
    }

    public function testEmptyAudienceMarksAsFailed(): void
    {
        $message = new AdherentMessage();
        $this->setEntityId($message, 100);
        $campaign = $this->buildCampaign($message, segmentId: 555);

        $adherentRepository = $this->createStub(AdherentRepository::class);
        $adherentRepository->method('findAdherentIdsForMessage')->willReturn([]);

        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('find')->willReturnCallback($this->buildFindCallback($campaign));

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $handler = $this->buildHandler($em, adherentRepository: $adherentRepository, bus: $bus);
        $handler(new PrepareCampaignAudienceMessage(7, 1));

        self::assertSame(PreparationStatusEnum::Failed, $campaign->getPreparationStatus());
        self::assertSame(BlockReasonEnum::Empty, $campaign->getBlockReason());
    }

    public function testHappyPathStagesAudienceAddedAndDispatchesFinalizeOnly(): void
    {
        $message = new AdherentMessage();
        $this->setEntityId($message, 100);
        $campaign = $this->buildCampaign($message, segmentId: 555);

        // 1500 ids, chunk grain 50 → ceil(1500/50) = 30 fan-out chunks.
        $adherentIds = range(1, 1500);

        $adherentRepository = $this->createStub(AdherentRepository::class);
        $adherentRepository->method('findAdherentIdsForMessage')->willReturn($adherentIds);

        $capturedRows = [];
        $memberRepository = $this->createMock(MailchimpStaticSegmentMemberRepository::class);
        $memberRepository->expects(self::once())->method('deleteBySegmentId');

        $bulkInsertHelper = $this->createMock(BulkInsertHelper::class);
        $bulkInsertHelper->expects(self::once())
            ->method('insertIgnore')
            ->with(
                'mailchimp_static_segment_member',
                self::callback(function (array $rows) use (&$capturedRows): bool {
                    $capturedRows = $rows;

                    return true;
                }),
            )
        ;

        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('find')->willReturnCallback($this->buildFindCallback($campaign));

        $dispatched = [];
        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::once()) // only the finalize — no per-chunk push step anymore
            ->method('dispatch')
            ->willReturnCallback(function (object $msg) use (&$dispatched): Envelope {
                $dispatched[] = $msg;

                return new Envelope($msg);
            })
        ;

        $handler = $this->buildHandler(
            $em,
            adherentRepository: $adherentRepository,
            memberRepository: $memberRepository,
            bulkInsertHelper: $bulkInsertHelper,
            bus: $bus,
        );
        $handler(new PrepareCampaignAudienceMessage(7, 1));

        // Exactly one dispatch, and it is the finalize (no ProcessAudienceChunkMessage).
        self::assertCount(1, $dispatched);
        self::assertInstanceOf(FinalizeCampaignAudienceMessage::class, $dispatched[0]);

        // Every staged row is Added (ready to send), and the chunk numbering uses the 50 grain.
        self::assertCount(1500, $capturedRows);
        $statuses = array_unique(array_column($capturedRows, 'processing_status'));
        self::assertSame([SegmentMemberStatusEnum::Added->value], $statuses);
        self::assertSame(1, $capturedRows[0]['chunk_number']);
        self::assertSame(30, $capturedRows[1499]['chunk_number']);

        $segment = $campaign->getMailchimpStaticSegment();
        self::assertSame(1500, $segment->expectedCount);
        self::assertSame(30, $segment->chunksTotal);
        self::assertSame(0, $segment->chunksDone);
        self::assertSame(PreparationStatusEnum::Preparing, $campaign->getPreparationStatus());
    }

    public function testIdempotenceRetryRedispatchesFinalizeOnly(): void
    {
        $message = new AdherentMessage();
        $this->setEntityId($message, 100);
        $campaign = $this->buildCampaign($message, segmentId: 555);
        $campaign->markAsPreparing($this->createStub(Adherent::class));
        $segment = $campaign->getMailchimpStaticSegment();
        $segment->chunksTotal = 10;
        $segment->chunksDone = 4;

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('find')->willReturn($campaign);
        $em->expects(self::never())->method('flush');

        // The audience is staged in a single bulk insert; the retry never reprocesses chunks.
        $memberRepository = $this->createMock(MailchimpStaticSegmentMemberRepository::class);
        $memberRepository->expects(self::never())->method('findChunksWithPending');
        $memberRepository->expects(self::never())->method('deleteBySegmentId');

        $dispatched = [];
        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::once())
            ->method('dispatch')
            ->willReturnCallback(function (object $msg) use (&$dispatched): Envelope {
                $dispatched[] = $msg;

                return new Envelope($msg);
            })
        ;

        $handler = $this->buildHandler($em, memberRepository: $memberRepository, bus: $bus);
        $handler(new PrepareCampaignAudienceMessage(7, 1));

        self::assertCount(1, $dispatched);
        self::assertInstanceOf(FinalizeCampaignAudienceMessage::class, $dispatched[0]);
    }

    public function testMailchimpFallbackWipesSegmentStagesPendingAndFansOutChunks(): void
    {
        $message = new AdherentMessage();
        $this->setEntityId($message, 100);
        $campaign = $this->buildCampaign($message, segmentId: 555);

        // 1500 ids, Mailchimp grain 500 → ceil(1500/500) = 3 push chunks.
        $adherentIds = range(1, 1500);
        $adherentRepository = $this->createStub(AdherentRepository::class);
        $adherentRepository->method('findAdherentIdsForMessage')->willReturn($adherentIds);

        $capturedRows = [];
        $memberRepository = $this->createMock(MailchimpStaticSegmentMemberRepository::class);
        $memberRepository->expects(self::once())->method('deleteBySegmentId');

        $bulkInsertHelper = $this->createMock(BulkInsertHelper::class);
        $bulkInsertHelper->expects(self::once())
            ->method('insertIgnore')
            ->with(
                'mailchimp_static_segment_member',
                self::callback(function (array $rows) use (&$capturedRows): bool {
                    $capturedRows = $rows;

                    return true;
                }),
            )
        ;

        $mailchimpObjectIdMapping = $this->createMock(MailchimpObjectIdMapping::class);
        $mailchimpObjectIdMapping->expects(self::once())->method('getMainListId')->willReturn('main-list');

        // Segment wiped on the remote list (555) before the refill; same list as the chunk push.
        $staticSegmentService = $this->createMock(MailchimpStaticSegmentServiceInterface::class);
        $staticSegmentService->expects(self::once())->method('update')->with(555, [], 'main-list')->willReturn(true);

        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('find')->willReturnCallback($this->buildFindCallback($campaign));

        $dispatched = [];
        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::exactly(4)) // 3 chunk pushes + 1 finalize
            ->method('dispatch')
            ->willReturnCallback(function (object $msg) use (&$dispatched): Envelope {
                $dispatched[] = $msg;

                return new Envelope($msg);
            })
        ;

        $handler = $this->buildHandler(
            $em,
            adherentRepository: $adherentRepository,
            memberRepository: $memberRepository,
            bulkInsertHelper: $bulkInsertHelper,
            bus: $bus,
            sendViaMailchimp: true,
            staticSegmentService: $staticSegmentService,
            mailchimpObjectIdMapping: $mailchimpObjectIdMapping,
        );
        $handler(new PrepareCampaignAudienceMessage(7, 1));

        $chunkMessages = array_filter($dispatched, static fn (object $m): bool => $m instanceof ProcessAudienceChunkMessage);
        self::assertCount(3, $chunkMessages);
        self::assertInstanceOf(FinalizeCampaignAudienceMessage::class, $dispatched[3]);

        // Rows staged Pending (awaiting the remote push), chunk numbering uses the 500 grain.
        self::assertCount(1500, $capturedRows);
        $statuses = array_unique(array_column($capturedRows, 'processing_status'));
        self::assertSame([SegmentMemberStatusEnum::Pending->value], $statuses);
        self::assertSame(1, $capturedRows[0]['chunk_number']);
        self::assertSame(3, $capturedRows[1499]['chunk_number']);

        $segment = $campaign->getMailchimpStaticSegment();
        self::assertSame(3, $segment->chunksTotal);
        self::assertSame(PreparationStatusEnum::Preparing, $campaign->getPreparationStatus());
    }

    public function testMailchimpFallbackRetryRedispatchesPendingChunksOnly(): void
    {
        $message = new AdherentMessage();
        $this->setEntityId($message, 100);
        $campaign = $this->buildCampaign($message, segmentId: 555);
        $campaign->markAsPreparing($this->createStub(Adherent::class));
        $segment = $campaign->getMailchimpStaticSegment();
        $segment->chunksTotal = 10;
        $segment->chunksDone = 4;

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('find')->willReturn($campaign);
        $em->expects(self::never())->method('flush');

        $memberRepository = $this->createMock(MailchimpStaticSegmentMemberRepository::class);
        $memberRepository->expects(self::once())->method('findChunksWithPending')->with(4242)->willReturn([5, 7]);
        $memberRepository->expects(self::never())->method('deleteBySegmentId');

        $dispatched = [];
        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::exactly(3)) // 2 pending chunks + finalize
            ->method('dispatch')
            ->willReturnCallback(function (object $msg) use (&$dispatched): Envelope {
                $dispatched[] = $msg;

                return new Envelope($msg);
            })
        ;

        $handler = $this->buildHandler($em, memberRepository: $memberRepository, bus: $bus, sendViaMailchimp: true);
        $handler(new PrepareCampaignAudienceMessage(7, 1));

        $chunkMessages = array_filter($dispatched, static fn (object $m): bool => $m instanceof ProcessAudienceChunkMessage);
        self::assertCount(2, $chunkMessages);
        self::assertInstanceOf(FinalizeCampaignAudienceMessage::class, $dispatched[2]);
    }

    public function testMailchimpFallbackFailsWhenRemoteSegmentIdMissing(): void
    {
        $message = new AdherentMessage();
        $this->setEntityId($message, 100);
        // Segment entity present but no remote staticSegmentId: the fallback cannot push to Mailchimp.
        $campaign = new MailchimpCampaign($message);
        $this->setEntityId($campaign, 7);
        $message->addMailchimpCampaign($campaign);
        $segment = new MailchimpStaticSegment($campaign);
        $this->setEntityId($segment, 4242);
        $campaign->setMailchimpStaticSegment($segment);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('find')->willReturn($campaign);
        $em->expects(self::once())->method('flush');

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $handler = $this->buildHandler($em, bus: $bus, sendViaMailchimp: true);
        $handler(new PrepareCampaignAudienceMessage(7, 1));

        self::assertSame(PreparationStatusEnum::Failed, $campaign->getPreparationStatus());
        self::assertSame(BlockReasonEnum::MailchimpUnavailable, $campaign->getBlockReason());
    }

    public function testMailchimpFallbackThrowsWhenRemoteSegmentWipeFails(): void
    {
        $message = new AdherentMessage();
        $this->setEntityId($message, 100);
        $campaign = $this->buildCampaign($message, segmentId: 555);

        $adherentRepository = $this->createStub(AdherentRepository::class);
        $adherentRepository->method('findAdherentIdsForMessage')->willReturn(range(1, 10));

        $mailchimpObjectIdMapping = $this->createStub(MailchimpObjectIdMapping::class);
        $mailchimpObjectIdMapping->method('getMainListId')->willReturn('main-list');

        // The remote wipe fails → the whole preparation must fail (blocks the send) and rethrow.
        $staticSegmentService = $this->createMock(MailchimpStaticSegmentServiceInterface::class);
        $staticSegmentService->expects(self::once())->method('update')->willReturn(false);

        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('find')->willReturnCallback($this->buildFindCallback($campaign));

        $handler = $this->buildHandler(
            $em,
            adherentRepository: $adherentRepository,
            sendViaMailchimp: true,
            staticSegmentService: $staticSegmentService,
            mailchimpObjectIdMapping: $mailchimpObjectIdMapping,
        );

        $this->expectException(\RuntimeException::class);

        try {
            $handler(new PrepareCampaignAudienceMessage(7, 1));
        } finally {
            self::assertSame(PreparationStatusEnum::Failed, $campaign->getPreparationStatus());
        }
    }

    private function buildHandler(
        EntityManagerInterface $em,
        ?AdherentRepository $adherentRepository = null,
        ?MailchimpStaticSegmentMemberRepository $memberRepository = null,
        ?BulkInsertHelper $bulkInsertHelper = null,
        ?MessageBusInterface $bus = null,
        ?LoggerInterface $logger = null,
        bool $sendViaMailchimp = false,
        ?MailchimpStaticSegmentServiceInterface $staticSegmentService = null,
        ?MailchimpObjectIdMapping $mailchimpObjectIdMapping = null,
    ): PrepareCampaignAudienceHandler {
        return new PrepareCampaignAudienceHandler(
            $em,
            $adherentRepository ?? $this->createStub(AdherentRepository::class),
            $memberRepository ?? $this->createStub(MailchimpStaticSegmentMemberRepository::class),
            $bulkInsertHelper ?? $this->createStub(BulkInsertHelper::class),
            $bus ?? $this->createStub(MessageBusInterface::class),
            $this->createStub(NormalizerInterface::class),
            $staticSegmentService ?? $this->createStub(MailchimpStaticSegmentServiceInterface::class),
            $mailchimpObjectIdMapping ?? $this->createStub(MailchimpObjectIdMapping::class),
            $sendViaMailchimp,
            $logger,
        );
    }

    private function buildCampaign(AdherentMessage $message, int $segmentId): MailchimpCampaign
    {
        $campaign = new MailchimpCampaign($message);
        $this->setEntityId($campaign, 7);
        $message->addMailchimpCampaign($campaign);
        $campaign->setStaticSegmentId($segmentId);
        $segment = new MailchimpStaticSegment($campaign);
        $this->setEntityId($segment, 4242);
        $segment->mailchimpSegmentId = $segmentId;
        $campaign->setMailchimpStaticSegment($segment);

        return $campaign;
    }

    private function setEntityId(object $entity, int $id): void
    {
        $reflection = new \ReflectionObject($entity);
        $property = $reflection->getProperty('id');
        $property->setValue($entity, $id);
    }

    private function buildFindCallback(MailchimpCampaign $campaign): \Closure
    {
        $adherent = $this->createStub(Adherent::class);

        return static function (string $class) use ($campaign, $adherent): ?object {
            return match ($class) {
                MailchimpCampaign::class => $campaign,
                Adherent::class => $adherent,
                default => null,
            };
        };
    }
}
