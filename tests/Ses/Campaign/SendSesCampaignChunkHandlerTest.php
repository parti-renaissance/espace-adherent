<?php

declare(strict_types=1);

namespace Tests\App\Ses\Campaign;

use App\AdherentMessage\MailchimpStatusEnum;
use App\AdherentMessage\Variable\Parser as VariableParser;
use App\AdherentMessage\Variable\Renderer\SesVariableRenderer;
use App\Doctrine\Utils\BulkInsertHelper;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\MailchimpStaticSegmentMember;
use App\Mailchimp\Campaign\Audience\SegmentMemberStatusEnum;
use App\Mailer\Template\Manager;
use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
use App\Repository\AdherentRepository;
use App\Repository\MailchimpCampaignRepository;
use App\Ses\Campaign\Handler\SendSesCampaignChunkHandler;
use App\Ses\Campaign\Message\ReconcileSendErroredRowMessage;
use App\Ses\Campaign\Message\SendSesCampaignChunkMessage;
use App\Ses\Campaign\Reach\CampaignReachInserter;
use App\Ses\Campaign\Reconciliation\SendErroredRowReconciler;
use App\Ses\Campaign\SesCampaignCompleter;
use App\Ses\Client\SesEmail;
use App\Ses\Client\SesEmailClient;
use App\Ses\Client\SesSendOutcome;
use App\Ses\Rendering\EmailCssInliner;
use App\Ses\Rendering\PreheaderExtractor;
use App\Ses\Rendering\SesMessageAssembler;
use App\Ses\Rendering\SesRecipientContextFactory;
use App\Ses\Rendering\SesRecipientEmailFactory;
use App\Ses\Unsubscribe\UnsubscribeUrlGenerator;
use AsyncAws\Core\Exception\Http\ClientException;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Lock\Exception\LockAcquiringException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\RateLimiter\LimiterInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;
use Tests\App\AbstractKernelTestCase;

#[Group('functional')]
class SendSesCampaignChunkHandlerTest extends AbstractKernelTestCase
{
    use SesCampaignFixturesTrait;

    /** Must match SendSesCampaignChunkHandler::RATE_LIMITER_KEY so the test drives the same bucket. */
    private const string RATE_KEY = 'ses:global';

    public function testSendsChunkRowsMarksSentCompletesCampaignAndRecordsReach(): void
    {
        $campaign = $this->createCampaign();
        $campaign->status = MailchimpStatusEnum::Sending;
        $first = $this->createSubscribedAdherent();
        $second = $this->createSubscribedAdherent();
        $this->addMember($campaign, $first, 1, SegmentMemberStatusEnum::Added);
        $this->addMember($campaign, $second, 1, SegmentMemberStatusEnum::Added);
        $this->manager->flush();

        // Each claimed row must be sent to its OWN recipient: the status/reach assertions below are keyed on row
        // ids, so they would stay green if every mail went to the same address — this is what pins the addressing.
        $expectedRecipients = [$first->getEmailAddress(), $second->getEmailAddress()];
        $sentTo = [];

        $client = $this->createMock(SesEmailClient::class);
        $client
            ->expects(self::exactly(2))
            ->method('sendEmail')
            ->with(self::callback(static fn (SesEmail $email): bool => \in_array($email->to, $expectedRecipients, true)))
            ->willReturnCallback(static function (SesEmail $email) use (&$sentTo): SesSendOutcome {
                $sentTo[] = $email->to;

                return SesSendOutcome::sent('ses-msg-'.$email->to);
            })
        ;

        $this->createHandler($client)(new SendSesCampaignChunkMessage($campaign->getId(), 1));

        $segmentId = $campaign->getMailchimpStaticSegment()->id;
        $messageId = $campaign->getMessage()->getId();

        self::assertEqualsCanonicalizing($expectedRecipients, $sentTo, 'each row is sent to its own recipient, exactly once');
        self::assertSame(2, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Sent));
        self::assertSame(MailchimpStatusEnum::Sent, $this->reloadStatus($campaign));
        self::assertSame(2, $this->countReach($messageId));
    }

    public function testFirstChunkLeavesCampaignSendingSecondChunkCompletesIt(): void
    {
        $campaign = $this->createCampaign();
        $campaign->status = MailchimpStatusEnum::Sending;
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->addMember($campaign, $this->createSubscribedAdherent(), 2, SegmentMemberStatusEnum::Added);
        $this->manager->flush();

        $segmentId = $campaign->getMailchimpStaticSegment()->id;
        $messageId = $campaign->getMessage()->getId();

        $client = $this->createMock(SesEmailClient::class);
        $client
            ->expects(self::exactly(2))
            ->method('sendEmail')
            ->willReturnCallback(static fn (SesEmail $email): SesSendOutcome => SesSendOutcome::sent('ses-msg-'.$email->to))
        ;
        $handler = $this->createHandler($client);

        // First chunk: one row left to send in chunk 2, so the campaign stays Sending and reach is not yet recorded.
        $handler(new SendSesCampaignChunkMessage($campaign->getId(), 1));
        self::assertSame(MailchimpStatusEnum::Sending, $this->reloadStatus($campaign));
        self::assertSame(0, $this->countReach($messageId));

        // Last chunk: no sendable row left -> campaign completed and reach recorded once for both rows.
        $handler(new SendSesCampaignChunkMessage($campaign->getId(), 2));
        self::assertSame(MailchimpStatusEnum::Sent, $this->reloadStatus($campaign));
        self::assertSame(2, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Sent));
        self::assertSame(2, $this->countReach($messageId));
    }

    public function testAlreadySentRowIsNotResent(): void
    {
        $campaign = $this->createCampaign();
        $campaign->status = MailchimpStatusEnum::Sending;
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Sent);
        $this->manager->flush();

        // Only the Added row is sendable: the SES client is hit exactly once.
        $client = $this->createMock(SesEmailClient::class);
        $client
            ->expects(self::once())
            ->method('sendEmail')
            ->willReturnCallback(static fn (SesEmail $email): SesSendOutcome => SesSendOutcome::sent('ses-msg-'.$email->to))
        ;

        $this->createHandler($client)(new SendSesCampaignChunkMessage($campaign->getId(), 1));

        $segmentId = $campaign->getMailchimpStaticSegment()->id;

        self::assertSame(2, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Sent));
        self::assertSame(MailchimpStatusEnum::Sent, $this->reloadStatus($campaign));
    }

    public function testPermanentRejectionMarksRowRefusedWithReasonAndExcludesFromReach(): void
    {
        $campaign = $this->createCampaign();
        $campaign->status = MailchimpStatusEnum::Sending;
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->manager->flush();

        // A permanent rejection (unverified/invalid address) is terminal but is NOT a delivery: the row is
        // closed as Refused with the SES reason recorded next to it, nothing bubbles up (unlike a transport
        // failure), the campaign still completes, and the recipient is excluded from the reach.
        $client = $this->createMock(SesEmailClient::class);
        $client
            ->expects(self::once())
            ->method('sendEmail')
            ->willReturnCallback(static fn (): SesSendOutcome => SesSendOutcome::rejected('Email address is not verified.'))
        ;

        $this->createHandler($client)(new SendSesCampaignChunkMessage($campaign->getId(), 1));

        $segmentId = $campaign->getMailchimpStaticSegment()->id;
        $messageId = $campaign->getMessage()->getId();

        self::assertSame(0, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Sent));
        self::assertSame(1, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Refused));
        self::assertSame('Email address is not verified.', $this->firstErrorMessage($segmentId));
        self::assertSame(MailchimpStatusEnum::Sent, $this->reloadStatus($campaign));
        self::assertSame(0, $this->countReach($messageId));
    }

    public function testSuppressionListRejectionMarksRecipientHardBounced(): void
    {
        $campaign = $this->createCampaign();
        $campaign->status = MailchimpStatusEnum::Sending;
        $recipient = $this->createSubscribedAdherent();
        $this->addMember($campaign, $recipient, 1, SegmentMemberStatusEnum::Added);
        $this->manager->flush();
        $email = $recipient->getEmailAddress();

        // SES refuses a recipient already on the account suppression list: a known-dead mailbox.
        $client = $this->createMock(SesEmailClient::class);
        $client
            ->expects(self::once())
            ->method('sendEmail')
            ->willReturnCallback(static fn (): SesSendOutcome => SesSendOutcome::rejected('Email address is on the suppression list for your account.'))
        ;

        $this->createHandler($client)(new SendSesCampaignChunkMessage($campaign->getId(), 1));

        $this->manager->clear();
        $reloaded = $this->getRepository(Adherent::class)->findOneByEmail($email);
        self::assertTrue($reloaded->isEmailHardBounced(), 'suppression-list rejection must flag the recipient');
    }

    public function testSenderConfigRejectionDoesNotMarkRecipient(): void
    {
        $campaign = $this->createCampaign();
        $campaign->status = MailchimpStatusEnum::Sending;
        $recipient = $this->createSubscribedAdherent();
        $this->addMember($campaign, $recipient, 1, SegmentMemberStatusEnum::Added);
        $this->manager->flush();
        $email = $recipient->getEmailAddress();

        // A sender-side config error is not the recipient's fault: live addresses must not be suppressed.
        $client = $this->createMock(SesEmailClient::class);
        $client
            ->expects(self::once())
            ->method('sendEmail')
            ->willReturnCallback(static fn (): SesSendOutcome => SesSendOutcome::rejected('MailFromDomainNotVerified: the sending domain is not verified.'))
        ;

        $this->createHandler($client)(new SendSesCampaignChunkMessage($campaign->getId(), 1));

        $this->manager->clear();
        $reloaded = $this->getRepository(Adherent::class)->findOneByEmail($email);
        self::assertFalse($reloaded->isEmailHardBounced(), 'sender-config rejection must not flag the recipient');
    }

    public function testThrottleReopensRowEscalatesAndRedispatchesWithIncrementedAttempt(): void
    {
        $campaign = $this->createCampaign();
        $campaign->status = MailchimpStatusEnum::Sending;
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->manager->flush();

        // Saturated limiter: the row throttles before any SES call.
        $client = $this->createMock(SesEmailClient::class);
        $client->expects(self::never())->method('sendEmail');

        // At THRESHOLD-1 with no progress, the re-dispatch crosses the alert threshold → exactly one error.
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                '[SES][Campaign] Send sustained throttling — chunk stuck, still retrying',
                self::callback(static fn (array $ctx): bool => 20 === $ctx['throttle_attempt']),
            )
        ;

        $dispatched = [];
        $this->createHandler($client, $this->spyBus($dispatched), $this->drainedLimiter(), $logger)(
            new SendSesCampaignChunkMessage($campaign->getId(), 1, 19),
        );

        $segmentId = $campaign->getMailchimpStaticSegment()->id;
        self::assertSame(1, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Added), 'the throttled row stays sendable');
        self::assertSame(0, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Sent));

        $redispatched = $this->firstChunkMessage($dispatched);
        self::assertSame(20, $redispatched['message']->throttleAttempt);
        self::assertTrue($this->hasDelayStamp($redispatched['stamps']));
    }

    public function testTokenIsReservedBeforeTheRowIsClaimed(): void
    {
        $campaign = $this->createCampaign();
        $campaign->status = MailchimpStatusEnum::Sending;
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->manager->flush();

        $client = $this->createMock(SesEmailClient::class);
        $client->expects(self::never())->method('sendEmail');

        // Saturated limiter: the reservation would sleep past the cap, so the chunk yields. The row must not have
        // been claimed at that point — a reservation can sleep for seconds, and a worker killed while sleeping on
        // a claimed row would strand it in Sending, where nothing can ever re-claim it.
        $dispatched = [];
        $this->createHandler($client, $this->spyBus($dispatched), $this->drainedLimiter())(
            new SendSesCampaignChunkMessage($campaign->getId(), 1),
        );

        $segmentId = $campaign->getMailchimpStaticSegment()->id;

        self::assertSame(1, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Added));
        // claimedAt is what pins the ordering: claiming first and only then reserving (the bug) would stamp it,
        // and reopening the row afterwards does not clear it. A never-claimed row has none.
        self::assertNull($this->firstClaimedAt($segmentId), 'the row was never claimed: the token is reserved first');
    }

    public function testProgressResetsThrottleCounterOnRedispatch(): void
    {
        $campaign = $this->createCampaign();
        $campaign->status = MailchimpStatusEnum::Sending;
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->manager->flush();

        // Exactly one token: the first row is sent, the second throttles.
        $client = $this->createMock(SesEmailClient::class);
        $client
            ->expects(self::once())
            ->method('sendEmail')
            ->willReturnCallback(static fn (SesEmail $email): SesSendOutcome => SesSendOutcome::sent('ses-msg-'.$email->to))
        ;

        $dispatched = [];
        $this->createHandler($client, $this->spyBus($dispatched), $this->limiterWithTokens(1))(
            new SendSesCampaignChunkMessage($campaign->getId(), 1, 5),
        );

        $segmentId = $campaign->getMailchimpStaticSegment()->id;
        self::assertSame(1, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Sent));
        self::assertSame(1, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Added), 'the throttled row is reopened');

        // The chunk made progress (one row sent), so the throttle counter resets to 0 despite starting at 5.
        self::assertSame(0, $this->firstChunkMessage($dispatched)['message']->throttleAttempt);
    }

    public function testAmbiguousSendErrorArmsTheDelayedReconciliation(): void
    {
        $campaign = $this->createCampaign();
        $campaign->status = MailchimpStatusEnum::Sending;
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->manager->flush();
        $segmentId = $campaign->getMailchimpStaticSegment()->id;

        $client = $this->createMock(SesEmailClient::class);
        $client->expects(self::once())->method('sendEmail')->willThrowException(new \RuntimeException('network down'));

        $dispatched = [];

        // The quarantine is not a dead end: an SES event may still prove the send happened, so the row is
        // scheduled for a later look. The SES failure must nonetheless keep propagating.
        try {
            $this->createHandler($client, $this->spyBus($dispatched))(new SendSesCampaignChunkMessage($campaign->getId(), 1));
            self::fail('An ambiguous send failure must still propagate.');
        } catch (\RuntimeException $exception) {
            self::assertSame('network down', $exception->getMessage());
        }

        $quarantinedRowId = $this->firstRowId($segmentId);
        $reconciliations = $this->dispatchedOfType($dispatched, ReconcileSendErroredRowMessage::class);

        self::assertCount(1, $reconciliations, 'the quarantined row is scheduled for reconciliation');
        self::assertSame($quarantinedRowId, $reconciliations[0]['message']->rowId);
        self::assertSame(0, $reconciliations[0]['message']->attempt, 'the first look is armed');
        self::assertTrue($this->hasDelayStamp($reconciliations[0]['stamps']), 'the look is delayed, not immediate');
    }

    public function testReconciliationDispatchFailureNeverMasksTheSesError(): void
    {
        $campaign = $this->createCampaign();
        $campaign->status = MailchimpStatusEnum::Sending;
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->manager->flush();
        $segmentId = $campaign->getMailchimpStaticSegment()->id;

        $client = $this->createMock(SesEmailClient::class);
        $client->expects(self::once())->method('sendEmail')->willThrowException(new \RuntimeException('network down'));

        // Broker down: arming the reconciliation fails. The row stays quarantined — the state it would have had
        // anyway — and the SES failure must still be the one that surfaces, never the dispatch failure.
        $brokenBus = $this->createStub(MessageBusInterface::class);
        $brokenBus->method('dispatch')->willThrowException(new \RuntimeException('broker unreachable'));

        try {
            $this->createHandler($client, $brokenBus)(new SendSesCampaignChunkMessage($campaign->getId(), 1));
            self::fail('The SES failure must propagate.');
        } catch (\RuntimeException $exception) {
            self::assertSame('network down', $exception->getMessage());
        }

        self::assertSame(1, $this->countByStatus($segmentId, SegmentMemberStatusEnum::SendErrored));
    }

    public function testAmbiguousSendErrorQuarantinesRowRethrowsAndDoesNotRedispatch(): void
    {
        $campaign = $this->createCampaign();
        $campaign->status = MailchimpStatusEnum::Sending;
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->manager->flush();

        $segmentId = $campaign->getMailchimpStaticSegment()->id;
        $messageId = $campaign->getMessage()->getId();

        // First recipient sends; the second hits an ambiguous failure (5xx/network — SES may already have sent).
        // The row must be quarantined terminally and the failure propagated for a bounded Messenger retry of the
        // rest of the chunk — never the unbounded re-dispatch of the 429 path, which would re-send it.
        $callCount = 0;
        $client = $this->createMock(SesEmailClient::class);
        $client
            ->expects(self::exactly(2))
            ->method('sendEmail')
            ->willReturnCallback(static function () use (&$callCount): SesSendOutcome {
                if (1 === ++$callCount) {
                    return SesSendOutcome::sent('ses-msg-1');
                }

                throw new \RuntimeException('SES transport failure');
            })
        ;

        $dispatched = [];

        try {
            $this->createHandler($client, $this->spyBus($dispatched))(new SendSesCampaignChunkMessage($campaign->getId(), 1));
            self::fail('An ambiguous send failure must still propagate so Messenger retries the chunk.');
        } catch (\RuntimeException $exception) {
            self::assertSame('SES transport failure', $exception->getMessage());
        }

        // One row delivered (stays Sent), the ambiguous one quarantined as SendErrored (terminal, never
        // reopened to Added, so never re-sent on retry).
        self::assertSame(1, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Sent));
        self::assertSame(1, $this->countByStatus($segmentId, SegmentMemberStatusEnum::SendErrored));
        self::assertSame(0, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Added));
        self::assertSame(0, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Sending));
        self::assertSame(
            [],
            $this->dispatchedOfType($dispatched, SendSesCampaignChunkMessage::class),
            'an ambiguous failure must not re-dispatch the chunk (that is the 429 path, and it would re-send)'
        );

        // The throw short-circuits completion: campaign still Sending, no reach recorded.
        self::assertSame(MailchimpStatusEnum::Sending, $this->reloadStatus($campaign));
        self::assertSame(0, $this->countReach($messageId));
    }

    public function testSendErroredRowIsNeverReSentOnChunkRedelivery(): void
    {
        $campaign = $this->createCampaign();
        $campaign->status = MailchimpStatusEnum::Sending;
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->manager->flush();

        $segmentId = $campaign->getMailchimpStaticSegment()->id;
        $messageId = $campaign->getMessage()->getId();
        $campaignId = $campaign->getId();

        // Exactly two SES calls across BOTH invocations: the redelivery must not re-send the quarantined row —
        // a third call would break this expectation and prove a double-send.
        $callCount = 0;
        $client = $this->createMock(SesEmailClient::class);
        $client
            ->expects(self::exactly(2))
            ->method('sendEmail')
            ->willReturnCallback(static function () use (&$callCount): SesSendOutcome {
                if (1 === ++$callCount) {
                    return SesSendOutcome::sent('ses-msg-1');
                }

                throw new \RuntimeException('SES transport failure');
            })
        ;

        $handler = $this->createHandler($client);

        // First delivery: one row sent, the other quarantined as SendErrored, and the failure propagates.
        try {
            $handler(new SendSesCampaignChunkMessage($campaignId, 1));
            self::fail('An ambiguous send failure must still propagate for a Messenger retry.');
        } catch (\RuntimeException $exception) {
            self::assertSame('SES transport failure', $exception->getMessage());
        }

        $this->manager->clear();

        // Redelivery of the same chunk: the Sent and SendErrored rows are both non-claimable, so no SES call is
        // made and the campaign completes on this clean pass.
        $handler(new SendSesCampaignChunkMessage($campaignId, 1));

        self::assertSame(1, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Sent));
        self::assertSame(1, $this->countByStatus($segmentId, SegmentMemberStatusEnum::SendErrored));
        self::assertSame(MailchimpStatusEnum::Sent, $this->reloadStatus($campaign));
        self::assertSame(1, $this->countReach($messageId));
    }

    public function testThrottle429RedispatchesChunkWithoutRethrow(): void
    {
        $campaign = $this->createCampaign();
        $campaign->status = MailchimpStatusEnum::Sending;
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->manager->flush();

        // SES returns 429 → SesEmailClient rethrows a ClientException. The request was rejected (not sent),
        // so the row is reopened and the chunk re-dispatched — no exception bubbles up.
        $throttle = new class('SES throttled') extends ClientException {
            public function __construct(string $message)
            {
                \RuntimeException::__construct($message);
            }
        };
        $client = $this->createMock(SesEmailClient::class);
        $client->expects(self::once())->method('sendEmail')->willThrowException($throttle);

        $dispatched = [];
        $this->createHandler($client, $this->spyBus($dispatched))(new SendSesCampaignChunkMessage($campaign->getId(), 1));

        $segmentId = $campaign->getMailchimpStaticSegment()->id;
        self::assertSame(1, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Added), 'the 429-throttled row is reopened');
        self::assertSame(0, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Sent));
        self::assertSame(1, $this->firstChunkMessage($dispatched)['message']->throttleAttempt);
    }

    public function testFailOpenSendsWithoutPacingWhenLimiterInfraFails(): void
    {
        $campaign = $this->createCampaign();
        $campaign->status = MailchimpStatusEnum::Sending;
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->manager->flush();

        // Redis/lock down → the limiter throws an infra exception; delivery must proceed unpaced (the 429
        // path stays the safety net). Never halt delivery on a limiter outage.
        $limiterInstance = $this->createStub(LimiterInterface::class);
        $limiterInstance->method('reserve')->willThrowException(new LockAcquiringException('redis down'));
        $limiter = $this->createStub(RateLimiterFactoryInterface::class);
        $limiter->method('create')->willReturn($limiterInstance);

        $client = $this->createMock(SesEmailClient::class);
        $client
            ->expects(self::once())
            ->method('sendEmail')
            ->willReturnCallback(static fn (SesEmail $email): SesSendOutcome => SesSendOutcome::sent('ses-msg-'.$email->to))
        ;

        $this->createHandler($client, limiter: $limiter)(new SendSesCampaignChunkMessage($campaign->getId(), 1));

        $segmentId = $campaign->getMailchimpStaticSegment()->id;
        self::assertSame(1, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Sent), 'delivery proceeds despite the limiter outage');
    }

    protected function tearDown(): void
    {
        $this->seq = 0;

        parent::tearDown();
    }

    private function createHandler(
        SesEmailClient $client,
        ?MessageBusInterface $bus = null,
        ?RateLimiterFactoryInterface $limiter = null,
        ?LoggerInterface $logger = null,
    ): SendSesCampaignChunkHandler {
        $memberRepository = self::getContainer()->get(MailchimpStaticSegmentMemberRepository::class);
        $campaignRepository = self::getContainer()->get(MailchimpCampaignRepository::class);
        $reachInserter = new CampaignReachInserter($memberRepository, self::getContainer()->get(BulkInsertHelper::class));
        // The completer and the reconciler dispatch on the SAME bus as the handler: a spy bus must see the
        // reconciliation and the stats refresh too, and a broken bus must break them the same way.
        $bus ??= self::getContainer()->get(MessageBusInterface::class);

        return new SendSesCampaignChunkHandler(
            $campaignRepository,
            $memberRepository,
            new SesMessageAssembler(
                self::getContainer()->get(Manager::class),
                new EmailCssInliner(new NullLogger()),
                new PreheaderExtractor(),
            ),
            new SesRecipientEmailFactory(new VariableParser(), new SesVariableRenderer(), new SesRecipientContextFactory(), self::getContainer()->get(UnsubscribeUrlGenerator::class)),
            $client,
            self::getContainer()->get(AdherentRepository::class),
            self::getContainer()->get(EntityManagerInterface::class),
            $bus,
            $limiter ?? $this->acceptingLimiter(),
            new SesCampaignCompleter($campaignRepository, $memberRepository, $reachInserter, $bus),
            new SendErroredRowReconciler($memberRepository, $reachInserter, $bus),
            $logger,
        );
    }

    /** A limiter with plenty of tokens: every reservation is granted instantly (no pacing effect). */
    private function acceptingLimiter(): RateLimiterFactoryInterface
    {
        return new RateLimiterFactory(
            ['id' => 'ses_test', 'policy' => 'token_bucket', 'limit' => 1000, 'rate' => ['interval' => '1 second', 'amount' => 1000]],
            new InMemoryStorage(),
        );
    }

    /** A token_bucket starting with exactly $tokens and an effectively frozen refill (1/hour). */
    private function limiterWithTokens(int $tokens): RateLimiterFactoryInterface
    {
        return new RateLimiterFactory(
            ['id' => 'ses_test', 'policy' => 'token_bucket', 'limit' => $tokens, 'rate' => ['interval' => '1 hour', 'amount' => 1]],
            new InMemoryStorage(),
        );
    }

    /** A saturated limiter: its only token is already spent, so the next reservation throttles. */
    private function drainedLimiter(): RateLimiterFactoryInterface
    {
        $factory = $this->limiterWithTokens(1);
        $factory->create(self::RATE_KEY)->consume(1);

        return $factory;
    }

    /**
     * @param list<array{message: object, stamps: array}> $dispatched
     *
     * @return array{message: SendSesCampaignChunkMessage, stamps: array}
     */
    private function firstChunkMessage(array $dispatched): array
    {
        $chunks = $this->dispatchedOfType($dispatched, SendSesCampaignChunkMessage::class);

        if ([] === $chunks) {
            self::fail('No SendSesCampaignChunkMessage was re-dispatched.');
        }

        return $chunks[0];
    }

    private function firstRowId(int $segmentId): int
    {
        return (int) $this->getRepository(MailchimpStaticSegmentMember::class)
            ->createQueryBuilder('m')
            ->select('m.id')
            ->where('IDENTITY(m.staticSegment) = :sid')
            ->setParameter('sid', $segmentId)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /** Scalar hydration hands back the raw DB value, not a \DateTimeImmutable. */
    private function firstClaimedAt(int $segmentId): ?string
    {
        return $this->getRepository(MailchimpStaticSegmentMember::class)
            ->createQueryBuilder('m')
            ->select('m.claimedAt')
            ->where('IDENTITY(m.staticSegment) = :sid')
            ->setParameter('sid', $segmentId)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function firstErrorMessage(int $segmentId): ?string
    {
        return $this->getRepository(MailchimpStaticSegmentMember::class)
            ->createQueryBuilder('m')
            ->select('m.errorMessage')
            ->where('IDENTITY(m.staticSegment) = :sid')
            ->setParameter('sid', $segmentId)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
