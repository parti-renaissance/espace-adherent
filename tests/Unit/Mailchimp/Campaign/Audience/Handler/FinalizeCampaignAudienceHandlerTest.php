<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp\Campaign\Audience\Handler;

use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpStaticSegment;
use App\Mailchimp\Campaign\Audience\Handler\FinalizeCampaignAudienceHandler;
use App\Mailchimp\Campaign\Audience\Message\FinalizeCampaignAudienceMessage;
use App\Mailchimp\Campaign\Audience\PreparationStatusEnum;
use App\Mailchimp\Campaign\Audience\SegmentMemberStatusEnum;
use App\Mailchimp\Campaign\Command\SendMailchimpCampaignCommand;
use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class FinalizeCampaignAudienceHandlerTest extends TestCase
{
    public function testCampaignNotFoundReturnsEarly(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('find')->with(MailchimpCampaign::class, 99)->willReturn(null);
        $em->expects(self::never())->method('flush');

        $repo = $this->createMock(MailchimpStaticSegmentMemberRepository::class);
        $repo->expects(self::never())->method('existsPending');

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $handler = $this->buildHandler($em, $repo, $bus);
        $handler(new FinalizeCampaignAudienceMessage(99));
    }

    public function testAlreadyReadyAndNotPendingSendIsIdempotentNoop(): void
    {
        $message = new AdherentMessage();
        $this->setEntityId($message, 100);
        $campaign = $this->buildCampaign($message, segmentId: 555);
        $campaign->markAsReady();

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('find')->with(MailchimpCampaign::class, 7)->willReturn($campaign);
        // The Ready guard branch always flushes (no-op at SQL level when no UoW changes).
        $em->expects(self::once())->method('flush');

        $repo = $this->createMock(MailchimpStaticSegmentMemberRepository::class);
        $repo->expects(self::never())->method('existsPending');
        $repo->expects(self::never())->method('aggregateStatusCounts');

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $handler = $this->buildHandler($em, $repo, $bus);
        $handler(new FinalizeCampaignAudienceMessage(7));
    }

    public function testReadyAndPendingSendDispatchesAndClears(): void
    {
        $message = new AdherentMessage();
        $this->setEntityId($message, 100);
        $campaign = $this->buildCampaign($message, segmentId: 555);
        $campaign->markAsReady();
        $campaign->markAsPendingSend();

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('find')->with(MailchimpCampaign::class, 7)->willReturn($campaign);
        $em->expects(self::once())->method('flush');

        $repo = $this->createMock(MailchimpStaticSegmentMemberRepository::class);
        $repo->expects(self::never())->method('existsPending');
        $repo->expects(self::never())->method('aggregateStatusCounts');

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(function (object $cmd): bool {
                return $cmd instanceof SendMailchimpCampaignCommand && 7 === $cmd->campaignId;
            }))
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $handler = $this->buildHandler($em, $repo, $bus);
        $handler(new FinalizeCampaignAudienceMessage(7));

        self::assertFalse($campaign->isPendingSend());
        self::assertSame(PreparationStatusEnum::Ready, $campaign->getPreparationStatus());
    }

    public function testReadyDispatchFailureLogsAndRethrowsKeepsPendingSend(): void
    {
        $message = new AdherentMessage();
        $this->setEntityId($message, 100);
        $campaign = $this->buildCampaign($message, segmentId: 555);
        $campaign->markAsReady();
        $campaign->markAsPendingSend();

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('find')->with(MailchimpCampaign::class, 7)->willReturn($campaign);
        $em->expects(self::never())->method('flush');

        $repo = $this->createStub(MailchimpStaticSegmentMemberRepository::class);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(SendMailchimpCampaignCommand::class))
            ->willThrowException(new \RuntimeException('broker down'))
        ;

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('error')
            ->with(
                '[AudienceFinalize] Auto-send dispatch failed',
                self::callback(function (array $context): bool {
                    return 7 === $context['campaign_id']
                        && $context['exception'] instanceof \RuntimeException
                        && 'broker down' === $context['exception']->getMessage();
                }),
            )
        ;

        $handler = $this->buildHandler($em, $repo, $bus, $logger);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('broker down');

        try {
            $handler(new FinalizeCampaignAudienceMessage(7));
        } finally {
            self::assertTrue($campaign->isPendingSend(), 'pendingSend must stay true so the Messenger retry replays the dispatch.');
        }
    }

    public function testStillPendingDelaysFinalization(): void
    {
        $message = new AdherentMessage();
        $this->setEntityId($message, 100);
        $campaign = $this->buildCampaign($message, segmentId: 555);
        $campaign->markAsPreparing($this->createStub(\App\Entity\Adherent::class));

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('find')->with(MailchimpCampaign::class, 7)->willReturn($campaign);
        $em->expects(self::never())->method('flush');

        $repo = $this->createMock(MailchimpStaticSegmentMemberRepository::class);
        $repo->expects(self::once())->method('existsPending')->with(4242)->willReturn(true);
        $repo->expects(self::never())->method('aggregateStatusCounts');

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $handler = $this->buildHandler($em, $repo, $bus);
        $handler(new FinalizeCampaignAudienceMessage(7));
    }

    public function testHappyPathAggregatesCountsAndMarksReady(): void
    {
        $message = new AdherentMessage();
        $this->setEntityId($message, 100);
        $campaign = $this->buildCampaign($message, segmentId: 555);
        $campaign->markAsPreparing($this->createStub(\App\Entity\Adherent::class));
        $segment = $campaign->getMailchimpStaticSegment();
        $segment->expectedCount = 1_000;

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('find')->with(MailchimpCampaign::class, 7)->willReturn($campaign);
        // First flush after markAsReady, second flush after dispatchAutoSendIfNeeded (no-op here).
        $em->expects(self::exactly(2))->method('flush');

        $repo = $this->createMock(MailchimpStaticSegmentMemberRepository::class);
        $repo->method('existsPending')->willReturn(false);
        $repo->expects(self::once())
            ->method('aggregateStatusCounts')
            ->with(4242)
            ->willReturn([
                SegmentMemberStatusEnum::Added->value => 950,
                SegmentMemberStatusEnum::Refused->value => 30,
                SegmentMemberStatusEnum::Errored->value => 20,
            ])
        ;

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $handler = $this->buildHandler($em, $repo, $bus);
        $handler(new FinalizeCampaignAudienceMessage(7));

        self::assertSame(950, $segment->preparedCount);
        self::assertSame(30, $segment->refusedCount);
        self::assertSame(20, $segment->erroredCount);
        self::assertNotNull($segment->builtAt);
        self::assertSame(PreparationStatusEnum::Ready, $campaign->getPreparationStatus());
        self::assertFalse($campaign->isPendingSend());
    }

    public function testPendingSendDispatchesSendCommandAndClearsFlag(): void
    {
        $message = new AdherentMessage();
        $this->setEntityId($message, 100);
        $campaign = $this->buildCampaign($message, segmentId: 555);
        $campaign->markAsPreparing($this->createStub(\App\Entity\Adherent::class));
        $campaign->markAsPendingSend();
        $segment = $campaign->getMailchimpStaticSegment();
        $segment->expectedCount = 1;

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('find')->with(MailchimpCampaign::class, 7)->willReturn($campaign);
        $em->expects(self::exactly(2))->method('flush');

        $repo = $this->createStub(MailchimpStaticSegmentMemberRepository::class);
        $repo->method('existsPending')->willReturn(false);
        $repo->method('aggregateStatusCounts')->willReturn([
            SegmentMemberStatusEnum::Added->value => 1,
        ]);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(function (object $cmd): bool {
                return $cmd instanceof SendMailchimpCampaignCommand && 7 === $cmd->campaignId;
            }))
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $handler = $this->buildHandler($em, $repo, $bus);
        $handler(new FinalizeCampaignAudienceMessage(7));

        self::assertFalse($campaign->isPendingSend());
        self::assertSame(PreparationStatusEnum::Ready, $campaign->getPreparationStatus());
    }

    public function testFinalizeDispatchFailureLogsAndRethrowsKeepsPendingSend(): void
    {
        $message = new AdherentMessage();
        $this->setEntityId($message, 100);
        $campaign = $this->buildCampaign($message, segmentId: 555);
        $campaign->markAsPreparing($this->createStub(\App\Entity\Adherent::class));
        $campaign->markAsPendingSend();
        $segment = $campaign->getMailchimpStaticSegment();
        $segment->expectedCount = 1;

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('find')->with(MailchimpCampaign::class, 7)->willReturn($campaign);
        // Only the first flush (after markAsReady) happens; the second is unreached due to throw.
        $em->expects(self::once())->method('flush');

        $repo = $this->createStub(MailchimpStaticSegmentMemberRepository::class);
        $repo->method('existsPending')->willReturn(false);
        $repo->method('aggregateStatusCounts')->willReturn([
            SegmentMemberStatusEnum::Added->value => 1,
        ]);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(SendMailchimpCampaignCommand::class))
            ->willThrowException(new \RuntimeException('broker down'))
        ;

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('error')
            ->with(
                '[AudienceFinalize] Auto-send dispatch failed',
                self::callback(function (array $context): bool {
                    return 7 === $context['campaign_id']
                        && $context['exception'] instanceof \RuntimeException;
                }),
            )
        ;

        $handler = $this->buildHandler($em, $repo, $bus, $logger);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('broker down');

        try {
            $handler(new FinalizeCampaignAudienceMessage(7));
        } finally {
            self::assertSame(PreparationStatusEnum::Ready, $campaign->getPreparationStatus(), 'markAsReady must have been persisted before the throw.');
            self::assertTrue($campaign->isPendingSend(), 'pendingSend must stay true so the Messenger retry replays via the Ready guard.');
        }
    }

    private function buildHandler(
        EntityManagerInterface $em,
        MailchimpStaticSegmentMemberRepository $repo,
        MessageBusInterface $bus,
        ?LoggerInterface $logger = null,
    ): FinalizeCampaignAudienceHandler {
        return new FinalizeCampaignAudienceHandler($em, $repo, $bus, $logger);
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
}
