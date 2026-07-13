<?php

declare(strict_types=1);

namespace Tests\App\Ses\Campaign;

use App\AdherentMessage\MailchimpStatusEnum;
use App\Doctrine\Utils\BulkInsertHelper;
use App\Mailchimp\Campaign\Audience\SegmentMemberStatusEnum;
use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
use App\Ses\Campaign\Handler\ReconcileSendErroredRowHandler;
use App\Ses\Campaign\Message\ReconcileSendErroredRowMessage;
use App\Ses\Campaign\Reach\CampaignReachInserter;
use App\Ses\Campaign\Reconciliation\SendErroredRowReconciler;
use App\Ses\Stats\Command\RefreshSesPublicationStatsCommand;
use PHPUnit\Framework\Attributes\Group;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Tests\App\AbstractKernelTestCase;

#[Group('functional')]
class ReconcileSendErroredRowHandlerTest extends AbstractKernelTestCase
{
    use SesCampaignFixturesTrait;

    public function testRowWithDeliveryMarkerIsPromotedAndReachRecorded(): void
    {
        $campaign = $this->createCampaign();
        $campaign->status = MailchimpStatusEnum::Sent;
        $row = $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::SendErrored);
        // The webhook attributed a Delivery to the row while it was quarantined: SES did accept the mail.
        $row->deliveredAt = new \DateTimeImmutable();
        $this->manager->flush();

        $segmentId = $campaign->getMailchimpStaticSegment()->id;
        $messageId = $campaign->getMessage()->getId();

        $dispatched = [];
        $this->createHandler($this->spyBus($dispatched))(new ReconcileSendErroredRowMessage($row->id));

        self::assertSame(1, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Sent), 'the proven row is promoted');
        self::assertSame(0, $this->countByStatus($segmentId, SegmentMemberStatusEnum::SendErrored));
        self::assertSame(1, $this->countReach($messageId), 'the recipient now counts in the reach');

        // The publication stats are materialized, so a promotion after completion needs its own refresh.
        $refreshes = $this->dispatchedOfType($dispatched, RefreshSesPublicationStatsCommand::class);
        self::assertCount(1, $refreshes);
        self::assertFalse($refreshes[0]['message']->autoReschedule, 'a one-off refresh, not a new refresh chain');
        self::assertSame([], $this->dispatchedOfType($dispatched, ReconcileSendErroredRowMessage::class), 'the row is settled');
    }

    public function testRowWithBounceMarkerIsPromotedToSent(): void
    {
        $campaign = $this->createCampaign();
        $campaign->status = MailchimpStatusEnum::Sent;
        $row = $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::SendErrored);
        // A bounce describes the DELIVERY, not the send: SES accepted the mail, so the row is Sent — exactly
        // where a nominal row that bounces ends up.
        $row->bouncedAt = new \DateTimeImmutable();
        $row->bounceSubType = 'General';
        $this->manager->flush();

        $segmentId = $campaign->getMailchimpStaticSegment()->id;

        $this->createHandler()(new ReconcileSendErroredRowMessage($row->id));

        self::assertSame(1, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Sent));
        self::assertSame(1, $this->countReach((int) $campaign->getMessage()->getId()), 'the reach counts sends, not deliveries');
    }

    public function testPromotedRowWithDeletedAdherentSkipsTheReach(): void
    {
        $campaign = $this->createCampaign();
        $campaign->status = MailchimpStatusEnum::Sent;
        $row = $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::SendErrored);
        $row->deliveredAt = new \DateTimeImmutable();
        $this->manager->flush();

        // The adherent was deleted since the send (the FK is ON DELETE SET NULL). The send did happen, so the
        // row is still promoted — but a reach row needs an adherent_id, so it is skipped rather than faked.
        $row->adherent = null;
        $this->manager->flush();

        $segmentId = $campaign->getMailchimpStaticSegment()->id;

        $dispatched = [];
        $this->createHandler($this->spyBus($dispatched))(new ReconcileSendErroredRowMessage($row->id));

        self::assertSame(1, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Sent));
        self::assertSame(0, $this->countReach((int) $campaign->getMessage()->getId()), 'no adherent, no reach');
        self::assertSame([], $this->dispatchedOfType($dispatched, ReconcileSendErroredRowMessage::class), 'the row is settled, not re-watched forever');
    }

    public function testRowWithoutMarkerSchedulesASecondPass(): void
    {
        $campaign = $this->createCampaign();
        $campaign->status = MailchimpStatusEnum::Sent;
        $row = $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::SendErrored);
        $this->manager->flush();

        $segmentId = $campaign->getMailchimpStaticSegment()->id;

        $dispatched = [];
        $this->createHandler($this->spyBus($dispatched))(new ReconcileSendErroredRowMessage($row->id));

        self::assertSame(1, $this->countByStatus($segmentId, SegmentMemberStatusEnum::SendErrored), 'no event, no promotion');
        self::assertSame(0, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Added), 'and above all, no reopening');
        self::assertSame(0, $this->countReach((int) $campaign->getMessage()->getId()));

        $reconciliations = $this->dispatchedOfType($dispatched, ReconcileSendErroredRowMessage::class);
        self::assertCount(1, $reconciliations, 'the row gets a second, later look');
        self::assertSame(1, $reconciliations[0]['message']->attempt);
        self::assertTrue($this->hasDelayStamp($reconciliations[0]['stamps']));
    }

    public function testRowWithoutMarkerAfterSecondPassIsLeftQuarantined(): void
    {
        $campaign = $this->createCampaign();
        $campaign->status = MailchimpStatusEnum::Sent;
        $row = $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::SendErrored);
        $this->manager->flush();

        $segmentId = $campaign->getMailchimpStaticSegment()->id;

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('Quarantined row still unconfirmed'),
                self::callback(static fn (array $context): bool => $context['row'] === $row->id),
            )
        ;

        $dispatched = [];
        $this->createHandler($this->spyBus($dispatched), $logger)(new ReconcileSendErroredRowMessage($row->id, 1));

        self::assertSame(1, $this->countByStatus($segmentId, SegmentMemberStatusEnum::SendErrored));
        self::assertSame([], $dispatched, 'the second pass is the last one: no further look, and never a reopening');
    }

    public function testAlreadySentRowStillGetsItsReach(): void
    {
        $campaign = $this->createCampaign();
        $campaign->status = MailchimpStatusEnum::Sent;
        // A row promoted by an earlier run that crashed between the promotion and the reach insert: the guarded
        // update no longer matches, so the reach must NOT be conditioned on having just promoted the row.
        $row = $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Sent);
        $row->deliveredAt = new \DateTimeImmutable();
        $this->manager->flush();

        $messageId = (int) $campaign->getMessage()->getId();

        $dispatched = [];
        $handler = $this->createHandler($this->spyBus($dispatched));
        $handler(new ReconcileSendErroredRowMessage($row->id));

        self::assertSame(1, $this->countReach($messageId), 'the missing reach is recovered');
        self::assertSame([], $this->dispatchedOfType($dispatched, RefreshSesPublicationStatsCommand::class), 'nothing was promoted: no stats refresh');

        // Replay: INSERT IGNORE keeps it at one row.
        $handler(new ReconcileSendErroredRowMessage($row->id));

        self::assertSame(1, $this->countReach($messageId), 'replaying the reconciliation never duplicates the reach');
    }

    private function createHandler(?MessageBusInterface $bus = null, ?LoggerInterface $logger = null): ReconcileSendErroredRowHandler
    {
        $bus ??= self::getContainer()->get(MessageBusInterface::class);
        $memberRepository = self::getContainer()->get(MailchimpStaticSegmentMemberRepository::class);

        $reconciler = new SendErroredRowReconciler(
            $memberRepository,
            new CampaignReachInserter($memberRepository, self::getContainer()->get(BulkInsertHelper::class)),
            $bus,
        );

        return new ReconcileSendErroredRowHandler($reconciler, $bus, $logger);
    }
}
