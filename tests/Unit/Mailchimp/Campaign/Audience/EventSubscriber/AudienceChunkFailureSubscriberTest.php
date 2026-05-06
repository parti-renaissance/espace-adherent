<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp\Campaign\Audience\EventSubscriber;

use App\Mailchimp\Campaign\Audience\EventSubscriber\AudienceChunkFailureSubscriber;
use App\Mailchimp\Campaign\Audience\Message\FinalizeCampaignAudienceMessage;
use App\Mailchimp\Campaign\Audience\Message\PrepareCampaignAudienceMessage;
use App\Mailchimp\Campaign\Audience\Message\ProcessAudienceChunkMessage;
use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\MessageBusInterface;

class AudienceChunkFailureSubscriberTest extends TestCase
{
    public function testWillRetryIsNoOp(): void
    {
        $repo = $this->createMock(MailchimpStaticSegmentMemberRepository::class);
        $repo->expects(self::never())->method('markChunkAsErroredByCampaignId');

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $event = new WorkerMessageFailedEvent(
            new Envelope(new ProcessAudienceChunkMessage(7, 3)),
            'mailchimp_batch',
            new \RuntimeException('temporary failure'),
        );
        $event->setForRetry();

        new AudienceChunkFailureSubscriber($repo, $bus)->onMessageFailed($event);
    }

    public function testNonChunkMessageIsIgnored(): void
    {
        $repo = $this->createMock(MailchimpStaticSegmentMemberRepository::class);
        $repo->expects(self::never())->method('markChunkAsErroredByCampaignId');

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $event = new WorkerMessageFailedEvent(
            new Envelope(new PrepareCampaignAudienceMessage(7, 1)),
            'mailchimp_campaign',
            new \RuntimeException('boom'),
        );

        new AudienceChunkFailureSubscriber($repo, $bus)->onMessageFailed($event);
    }

    public function testDefinitiveFailureMarksChunkAndDispatchesFinalize(): void
    {
        $repo = $this->createMock(MailchimpStaticSegmentMemberRepository::class);
        $repo->expects(self::once())
            ->method('markChunkAsErroredByCampaignId')
            ->with(7, 3, 'Mailchimp HTTP 500')
            ->willReturn(500)
        ;

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(function (object $msg): bool {
                return $msg instanceof FinalizeCampaignAudienceMessage && 7 === $msg->mailchimpCampaignId;
            }))
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $event = new WorkerMessageFailedEvent(
            new Envelope(new ProcessAudienceChunkMessage(7, 3)),
            'mailchimp_batch',
            new \RuntimeException('Mailchimp HTTP 500'),
        );

        new AudienceChunkFailureSubscriber($repo, $bus)->onMessageFailed($event);
    }

    public function testErrorMessageIsTruncatedTo2000Chars(): void
    {
        $longMessage = str_repeat('A', 5_000);

        $repo = $this->createMock(MailchimpStaticSegmentMemberRepository::class);
        $repo->expects(self::once())
            ->method('markChunkAsErroredByCampaignId')
            ->with(7, 3, self::callback(function (string $msg): bool {
                return 2_000 === mb_strlen($msg);
            }))
            ->willReturn(0)
        ;

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->method('dispatch')->willReturn(new Envelope(new \stdClass()));

        $event = new WorkerMessageFailedEvent(
            new Envelope(new ProcessAudienceChunkMessage(7, 3)),
            'mailchimp_batch',
            new \RuntimeException($longMessage),
        );

        new AudienceChunkFailureSubscriber($repo, $bus)->onMessageFailed($event);
    }

    public function testGetSubscribedEventsRegistersWorkerMessageFailedEvent(): void
    {
        self::assertSame(
            [WorkerMessageFailedEvent::class => 'onMessageFailed'],
            AudienceChunkFailureSubscriber::getSubscribedEvents(),
        );
    }
}
