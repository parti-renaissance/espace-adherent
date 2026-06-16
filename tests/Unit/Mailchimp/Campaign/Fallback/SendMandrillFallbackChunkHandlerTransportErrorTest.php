<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp\Campaign\Fallback;

use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpStaticSegment;
use App\Mailchimp\Campaign\Fallback\Handler\SendMandrillFallbackChunkHandler;
use App\Mailchimp\Campaign\Fallback\MandrillCampaignPayloadBuilder;
use App\Mailchimp\Campaign\Fallback\MandrillResponseParser;
use App\Mailchimp\Campaign\Fallback\Message\SendMandrillFallbackChunkMessage;
use App\Mailer\EmailClientInterface;
use App\Mailer\Exception\MailerException;
use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
use App\Repository\AdherentMessage\MandrillFallbackChunkRepository;
use App\Repository\MailchimpCampaignRepository;
use PHPUnit\Framework\TestCase;

class SendMandrillFallbackChunkHandlerTransportErrorTest extends TestCase
{
    public function testTransportErrorReopensChunkAndRethrows(): void
    {
        $campaignId = 42;
        $chunkNumber = 1;

        $message = new AdherentMessage();
        $message->setSubject('s');
        $message->setContent('<p>x</p>');
        $campaign = new MailchimpCampaign($message);
        $segment = new MailchimpStaticSegment($campaign);
        $segment->id = 7;
        $campaign->setMailchimpStaticSegment($segment);

        $campaignRepository = $this->createMock(MailchimpCampaignRepository::class);
        $campaignRepository->expects(self::once())->method('find')->with($campaignId)->willReturn($campaign);

        $chunkRepository = $this->createMock(MandrillFallbackChunkRepository::class);
        $chunkRepository->expects(self::once())->method('claimForSending')->with($campaignId, $chunkNumber)->willReturn(true);
        // The retry-safety contract: a transport error reopens the chunk for a clean retry.
        $chunkRepository->expects(self::once())->method('markPending')->with($campaignId, $chunkNumber);
        $chunkRepository->expects(self::never())->method('markSent');

        $recipients = [['email' => 'a@test.dev', 'firstName' => 'A', 'lastName' => 'B', 'gender' => 'female', 'publicId' => 'P1']];
        $memberRepository = $this->createMock(MailchimpStaticSegmentMemberRepository::class);
        $memberRepository->expects(self::once())->method('findRecipientsForMandrillByChunk')->with(7, $chunkNumber)->willReturn($recipients);

        $payloadBuilder = $this->createMock(MandrillCampaignPayloadBuilder::class);
        $payloadBuilder->expects(self::once())->method('build')->with($message, '<p>rendered</p>', $recipients)->willReturn(['message' => ['html' => 'x']]);

        $emailClient = new class implements EmailClientInterface {
            public function sendEmail(string $email, bool $resend = false, bool $useTemplateEndpoint = true): string
            {
                throw new MailerException('transport down');
            }
        };

        $handler = new SendMandrillFallbackChunkHandler(
            $campaignRepository,
            $memberRepository,
            $chunkRepository,
            $payloadBuilder,
            new MandrillResponseParser(),
            $emailClient,
        );

        $this->expectException(MailerException::class);

        $handler(new SendMandrillFallbackChunkMessage($campaignId, $chunkNumber, '<p>rendered</p>'));
    }
}
