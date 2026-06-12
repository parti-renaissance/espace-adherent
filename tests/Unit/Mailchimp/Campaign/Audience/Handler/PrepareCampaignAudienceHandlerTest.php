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

    public function testHappyPathDispatchesChunksAndFinalize(): void
    {
        $message = new AdherentMessage();
        $this->setEntityId($message, 100);
        $campaign = $this->buildCampaign($message, segmentId: 555);

        // 1500 ids → ceil(1500/500) = 3 chunks
        $adherentIds = range(1, 1500);

        $adherentRepository = $this->createStub(AdherentRepository::class);
        $adherentRepository->method('findAdherentIdsForMessage')->willReturn($adherentIds);

        $memberRepository = $this->createMock(MailchimpStaticSegmentMemberRepository::class);
        $memberRepository->expects(self::once())->method('deleteBySegmentId');

        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('find')->willReturnCallback($this->buildFindCallback($campaign));

        $staticSegmentService = $this->createMock(MailchimpStaticSegmentServiceInterface::class);
        $staticSegmentService->expects(self::once())
            ->method('update')
            ->with(555, [], 'main-list')
            ->willReturn(true)
        ;

        $dispatched = [];
        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::exactly(4)) // 3 chunks + 1 finalize
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
            staticSegmentService: $staticSegmentService,
            bus: $bus,
        );
        $handler(new PrepareCampaignAudienceMessage(7, 1));

        self::assertCount(4, $dispatched);
        $chunkMessages = array_filter($dispatched, fn ($m) => $m instanceof ProcessAudienceChunkMessage);
        $finalizeMessages = array_filter($dispatched, fn ($m) => $m instanceof FinalizeCampaignAudienceMessage);
        self::assertCount(3, $chunkMessages);
        self::assertCount(1, $finalizeMessages);

        $segment = $campaign->getMailchimpStaticSegment();
        self::assertSame(1500, $segment->expectedCount);
        self::assertSame(3, $segment->chunksTotal);
        self::assertSame(0, $segment->chunksDone);
        self::assertSame(PreparationStatusEnum::Preparing, $campaign->getPreparationStatus());
    }

    public function testWipeFailureFailsPreparationAndRethrows(): void
    {
        $message = new AdherentMessage();
        $this->setEntityId($message, 100);
        $campaign = $this->buildCampaign($message, segmentId: 555);

        $adherentRepository = $this->createStub(AdherentRepository::class);
        $adherentRepository->method('findAdherentIdsForMessage')->willReturn(range(1, 93));

        $memberRepository = $this->createMock(MailchimpStaticSegmentMemberRepository::class);
        // Local rows are cleared before the wipe (so the catch starts from a clean local state too).
        $memberRepository->expects(self::once())->method('deleteBySegmentId');

        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('find')->willReturnCallback($this->buildFindCallback($campaign));

        $staticSegmentService = $this->createMock(MailchimpStaticSegmentServiceInterface::class);
        $staticSegmentService
            ->expects(self::once())
            ->method('update')
            ->with(555, [], 'main-list')
            ->willReturn(false)
        ;

        $bus = $this->createMock(MessageBusInterface::class);
        // The wipe failure aborts before the dispatch loop — no chunk, no finalize.
        $bus->expects(self::never())->method('dispatch');

        $handler = $this->buildHandler(
            $em,
            adherentRepository: $adherentRepository,
            memberRepository: $memberRepository,
            staticSegmentService: $staticSegmentService,
            bus: $bus,
        );

        try {
            $handler(new PrepareCampaignAudienceMessage(7, 1));
            self::fail('Expected RuntimeException not thrown');
        } catch (\RuntimeException $e) {
            self::assertStringContainsString('Failed to wipe Mailchimp static segment 555', $e->getMessage());
        }

        // The catch in __invoke ran failPreparation() + set errorSummary + flushed before re-throwing.
        self::assertSame(PreparationStatusEnum::Failed, $campaign->getPreparationStatus());
        self::assertSame(BlockReasonEnum::MailchimpUnavailable, $campaign->getBlockReason());
        self::assertNotNull($campaign->getMailchimpStaticSegment()->errorSummary);
    }

    public function testIdempotenceRetryRedispatchesOnlyPendingChunks(): void
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
        $memberRepository->expects(self::once())
            ->method('findChunksWithPending')
            ->with(4242)
            ->willReturn([5, 6, 7, 8, 9])
        ;
        // No deleteBySegmentId in the retry branch.
        $memberRepository->expects(self::never())->method('deleteBySegmentId');

        $staticSegmentService = $this->createMock(MailchimpStaticSegmentServiceInterface::class);
        $staticSegmentService->expects(self::never())->method('update');

        $dispatched = [];
        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::exactly(6)) // 5 pending chunks + 1 finalize
            ->method('dispatch')
            ->willReturnCallback(function (object $msg) use (&$dispatched): Envelope {
                $dispatched[] = $msg;

                return new Envelope($msg);
            })
        ;

        $handler = $this->buildHandler(
            $em,
            memberRepository: $memberRepository,
            staticSegmentService: $staticSegmentService,
            bus: $bus,
        );
        $handler(new PrepareCampaignAudienceMessage(7, 1));

        $chunkNumbers = array_map(
            fn ($m) => $m->chunkNumber,
            array_filter($dispatched, fn ($m) => $m instanceof ProcessAudienceChunkMessage),
        );
        self::assertSame([5, 6, 7, 8, 9], array_values($chunkNumbers));
    }

    private function buildHandler(
        EntityManagerInterface $em,
        ?MailchimpStaticSegmentServiceInterface $staticSegmentService = null,
        ?AdherentRepository $adherentRepository = null,
        ?MailchimpStaticSegmentMemberRepository $memberRepository = null,
        ?BulkInsertHelper $bulkInsertHelper = null,
        ?MessageBusInterface $bus = null,
        ?LoggerInterface $logger = null,
    ): PrepareCampaignAudienceHandler {
        $mapping = $this->createStub(MailchimpObjectIdMapping::class);
        $mapping->method('getMainListId')->willReturn('main-list');

        return new PrepareCampaignAudienceHandler(
            $em,
            $staticSegmentService ?? $this->createStub(MailchimpStaticSegmentServiceInterface::class),
            $mapping,
            $adherentRepository ?? $this->createStub(AdherentRepository::class),
            $memberRepository ?? $this->createStub(MailchimpStaticSegmentMemberRepository::class),
            $bulkInsertHelper ?? $this->createStub(BulkInsertHelper::class),
            $bus ?? $this->createStub(MessageBusInterface::class),
            $this->createStub(NormalizerInterface::class),
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
