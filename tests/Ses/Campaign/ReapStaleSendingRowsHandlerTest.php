<?php

declare(strict_types=1);

namespace Tests\App\Ses\Campaign;

use App\AdherentMessage\MailchimpStatusEnum;
use App\Doctrine\Utils\BulkInsertHelper;
use App\Mailchimp\Campaign\Audience\SegmentMemberStatusEnum;
use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
use App\Repository\MailchimpCampaignRepository;
use App\Ses\Campaign\Handler\ReapStaleSendingRowsHandler;
use App\Ses\Campaign\Message\ReapStaleSendingRowsMessage;
use App\Ses\Campaign\Message\ReconcileSendErroredRowMessage;
use App\Ses\Campaign\Reach\CampaignReachInserter;
use App\Ses\Campaign\Reconciliation\SendErroredRowReconciler;
use App\Ses\Campaign\SesCampaignCompleter;
use PHPUnit\Framework\Attributes\Group;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Tests\App\AbstractKernelTestCase;

/**
 * A row is claimed (Added -> Sending) just before its SES call. A worker killed in that window — SIGKILL on a
 * rolling deploy — leaves it Sending forever: only Added rows are ever claimed again, while Sending rows keep
 * counting in countRemainingToSend, so the campaign would never complete. This is the watchdog that recovers it.
 */
#[Group('functional')]
class ReapStaleSendingRowsHandlerTest extends AbstractKernelTestCase
{
    use SesCampaignFixturesTrait;

    public function testAbandonedRowIsQuarantinedNotReopenedAndTheCampaignCompletes(): void
    {
        $campaign = $this->createCampaign();
        $campaign->status = MailchimpStatusEnum::Sending;
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Sent);
        $abandoned = $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Sending);
        $abandoned->claimedAt = new \DateTimeImmutable('-20 minutes');
        $this->manager->flush();

        $segmentId = $campaign->getMailchimpStaticSegment()->id;
        $messageId = $campaign->getMessage()->getId();
        $abandonedId = $abandoned->id;

        $dispatched = [];
        $this->createHandler($this->spyBus($dispatched))(new ReapStaleSendingRowsMessage($campaign->getId()));

        // Quarantined, NOT reopened: the dead worker may have died right after SES accepted the send, so the
        // outcome is ambiguous. Reopening (-> Added) would let the row be sent a second time.
        self::assertSame(1, $this->countByStatus($segmentId, SegmentMemberStatusEnum::SendErrored));
        self::assertSame(0, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Added), 'an abandoned row is never reopened: that would risk a double-send');
        self::assertSame(0, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Sending));

        // The reaped row unblocked the completion the dead chunk could never reach.
        self::assertSame(MailchimpStatusEnum::Sent, $this->reloadStatus($campaign));
        self::assertSame(1, $this->countReach($messageId), 'only the row actually sent counts in the reach');

        // It joins the normal quarantine path: an SES event may still prove the send happened and promote it.
        $reconciliations = $this->dispatchedOfType($dispatched, ReconcileSendErroredRowMessage::class);
        self::assertCount(1, $reconciliations);
        self::assertSame($abandonedId, $reconciliations[0]['message']->rowId);

        self::assertSame([], $this->dispatchedOfType($dispatched, ReapStaleSendingRowsMessage::class), 'nothing left to watch: the watchdog stops');
    }

    public function testRowStillLegitimatelySendingIsLeftAloneAndTheWatchdogRearms(): void
    {
        $campaign = $this->createCampaign();
        $campaign->status = MailchimpStatusEnum::Sending;
        $inFlight = $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Sending);
        $inFlight->claimedAt = new \DateTimeImmutable();
        $this->manager->flush();

        $segmentId = $campaign->getMailchimpStaticSegment()->id;

        $dispatched = [];
        $this->createHandler($this->spyBus($dispatched))(new ReapStaleSendingRowsMessage($campaign->getId(), 3));

        // A worker is very plausibly still sending this one: touching it would be the double-send we guard against.
        self::assertSame(1, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Sending), 'a freshly claimed row is not reaped');
        self::assertSame(0, $this->countByStatus($segmentId, SegmentMemberStatusEnum::SendErrored));
        self::assertSame(MailchimpStatusEnum::Sending, $this->reloadStatus($campaign));

        $rearmed = $this->dispatchedOfType($dispatched, ReapStaleSendingRowsMessage::class);
        self::assertCount(1, $rearmed, 'work remains: the watchdog schedules its next cycle');
        self::assertSame(4, $rearmed[0]['message']->cycle);
        self::assertTrue($this->hasDelayStamp($rearmed[0]['stamps']), 'the next cycle is delayed, not immediate');
    }

    public function testWatchdogStopsOnceTheCampaignLeftSending(): void
    {
        $campaign = $this->createCampaign();
        $campaign->status = MailchimpStatusEnum::Sent;
        $abandoned = $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Sending);
        $abandoned->claimedAt = new \DateTimeImmutable('-20 minutes');
        $this->manager->flush();

        $segmentId = $campaign->getMailchimpStaticSegment()->id;

        $dispatched = [];
        $this->createHandler($this->spyBus($dispatched))(new ReapStaleSendingRowsMessage($campaign->getId()));

        // The campaign is closed: the watchdog must not keep cycling forever on it, nor touch its rows.
        self::assertSame(1, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Sending));
        self::assertSame([], $dispatched, 'a closed campaign arms nothing');
    }

    public function testWatchdogGivesUpAndAlertsAfterTooManyCycles(): void
    {
        $campaign = $this->createCampaign();
        $campaign->status = MailchimpStatusEnum::Sending;
        $inFlight = $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Sending);
        $inFlight->claimedAt = new \DateTimeImmutable();
        $this->manager->flush();

        // A campaign still sending a full day later is stuck on something the reaper cannot fix: stop the
        // watchdog (an endless self-re-arming message is its own incident) and raise it instead.
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                '[SES][Campaign] Campaign still sending after the watchdog gave up — needs a look',
                self::callback(static fn (array $ctx): bool => 288 === $ctx['cycles']),
            )
        ;

        $dispatched = [];
        $this->createHandler($this->spyBus($dispatched), $logger)(new ReapStaleSendingRowsMessage($campaign->getId(), 288));

        self::assertSame([], $this->dispatchedOfType($dispatched, ReapStaleSendingRowsMessage::class), 'the watchdog does not re-arm forever');
    }

    protected function tearDown(): void
    {
        $this->seq = 0;

        parent::tearDown();
    }

    private function createHandler(MessageBusInterface $bus, ?LoggerInterface $logger = null): ReapStaleSendingRowsHandler
    {
        $memberRepository = self::getContainer()->get(MailchimpStaticSegmentMemberRepository::class);
        $campaignRepository = self::getContainer()->get(MailchimpCampaignRepository::class);
        $reachInserter = new CampaignReachInserter($memberRepository, self::getContainer()->get(BulkInsertHelper::class));

        return new ReapStaleSendingRowsHandler(
            $campaignRepository,
            $memberRepository,
            new SesCampaignCompleter($campaignRepository, $memberRepository, $reachInserter, $bus),
            new SendErroredRowReconciler($memberRepository, $reachInserter, $bus),
            $bus,
            $logger,
        );
    }
}
