<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp\Campaign\Audience;

use App\Mailchimp\Campaign\Audience\Message\FinalizeCampaignAudienceMessage;
use App\Mailchimp\Campaign\Audience\Message\MailchimpAudienceMessageInterface;
use App\Mailchimp\Campaign\Audience\Message\PrepareCampaignAudienceMessage;
use App\Ses\Campaign\Message\SesCampaignMessageInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The transport of these messages is decided by which interfaces they carry, so the interfaces are the
 * contract worth pinning.
 *
 * They used to implement SesCampaignMessageInterface, which routed the audience preparation of a
 * *Mailchimp* campaign onto `ses_campaign` — a queue the single worker drains after `mailchimp_batch`.
 * Messenger empties the transports of a `messenger:consume a b c` in order, so the preparation sat behind
 * the batch queue: 10 then 13 minutes before a 134k-recipient campaign started (2026-07-16).
 *
 * Carrying BOTH interfaces would be worse than the bug it replaces: Messenger collects the senders of
 * every routing entry a message matches, so the preparation would be dispatched to `mailchimp_audience`
 * AND `ses_campaign` — and run twice.
 */
class AudienceMessageRoutingTest extends TestCase
{
    public static function provideAudienceMessages(): iterable
    {
        yield 'prepare' => [new PrepareCampaignAudienceMessage(1, 2)];
        yield 'finalize' => [new FinalizeCampaignAudienceMessage(1)];
    }

    #[DataProvider('provideAudienceMessages')]
    public function testAudienceMessagesRouteToTheirOwnTransport(object $message): void
    {
        self::assertInstanceOf(MailchimpAudienceMessageInterface::class, $message);
    }

    #[DataProvider('provideAudienceMessages')]
    public function testAudienceMessagesNoLongerBorrowTheSesCampaignTransport(object $message): void
    {
        self::assertNotInstanceOf(
            SesCampaignMessageInterface::class,
            $message,
            'carrying both interfaces would dispatch the preparation to two transports and run it twice',
        );
    }
}
