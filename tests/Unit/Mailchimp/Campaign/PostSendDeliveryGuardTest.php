<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp\Campaign;

use App\AdherentMessage\MailchimpStatusEnum;
use App\Mailchimp\Campaign\DeliveryDecisionEnum;
use App\Mailchimp\Campaign\PostSendDeliveryGuard;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PostSendDeliveryGuardTest extends TestCase
{
    #[DataProvider('decisionProvider')]
    public function testEvaluateReturnsExpectedDecision(
        MailchimpStatusEnum $status,
        ?int $emailsSent,
        ?int $preparedCount,
        bool $sendingWindowExhausted,
        bool $confirmWindowExhausted,
        DeliveryDecisionEnum $expected,
    ): void {
        $decision = new PostSendDeliveryGuard()->evaluate($status, $emailsSent, $preparedCount, $sendingWindowExhausted, $confirmWindowExhausted);

        self::assertSame($expected, $decision->kind);
    }

    public static function decisionProvider(): iterable
    {
        // Healthy: at least one email left short-circuits everything (even while still sending).
        yield 'delivered, sent' => [MailchimpStatusEnum::Sent, 100, 93, false, true, DeliveryDecisionEnum::Ok];
        yield 'delivered while still sending' => [MailchimpStatusEnum::Sending, 5, 93, false, false, DeliveryDecisionEnum::Ok];

        // Terminal sent + confirmed zero: the only real KO, and only once the confirmation window ends.
        yield 'sent zero, confirm not exhausted → pending' => [MailchimpStatusEnum::Sent, 0, 93, false, false, DeliveryDecisionEnum::Pending];
        yield 'sent zero, confirm exhausted → failed' => [MailchimpStatusEnum::Sent, 0, 93, false, true, DeliveryDecisionEnum::Failed];

        // Unreadable report (null) is never a confirmed zero.
        yield 'sent unreadable, confirm not exhausted → pending' => [MailchimpStatusEnum::Sent, null, 93, false, false, DeliveryDecisionEnum::Pending];
        yield 'sent unreadable, confirm exhausted → unverifiable' => [MailchimpStatusEnum::Sent, null, 93, false, true, DeliveryDecisionEnum::Unverifiable];

        // Still sending is NEVER a failure/fallback (double-send guard); the sending window governs.
        yield 'sending zero, sending not exhausted → pending' => [MailchimpStatusEnum::Sending, 0, 93, false, false, DeliveryDecisionEnum::Pending];
        yield 'sending zero, sending exhausted → still sending' => [MailchimpStatusEnum::Sending, 0, 93, true, false, DeliveryDecisionEnum::StillSending];
        yield 'sending, prepared unknown, sending exhausted → still sending' => [MailchimpStatusEnum::Sending, null, null, true, false, DeliveryDecisionEnum::StillSending];
        // KEY decoupling: an exhausted CONFIRMATION window must NOT turn a still-sending campaign into
        // a KO. It keeps polling on the sending window until the send actually reaches "sent".
        yield 'sending zero, confirm exhausted but sending not → pending' => [MailchimpStatusEnum::Sending, 0, 93, false, true, DeliveryDecisionEnum::Pending];

        // Stuck before sending (save/schedule/error): bounded by the confirmation window, alert only.
        yield 'save zero, confirm not exhausted → pending' => [MailchimpStatusEnum::Save, 0, 93, false, false, DeliveryDecisionEnum::Pending];
        yield 'save zero, confirm exhausted → not sending' => [MailchimpStatusEnum::Save, 0, 93, false, true, DeliveryDecisionEnum::NotSending];
        yield 'error zero, confirm exhausted → not sending' => [MailchimpStatusEnum::Error, 0, 93, false, true, DeliveryDecisionEnum::NotSending];

        // preparedCount unknown/zero never returns a silent Ok when a send happened.
        yield 'sent zero, prepared unknown, confirm exhausted → unverifiable' => [MailchimpStatusEnum::Sent, 0, null, false, true, DeliveryDecisionEnum::Unverifiable];
        yield 'sent zero, prepared zero, confirm exhausted → unverifiable' => [MailchimpStatusEnum::Sent, 0, 0, false, true, DeliveryDecisionEnum::Unverifiable];
        yield 'save zero, prepared unknown, confirm exhausted → unverifiable' => [MailchimpStatusEnum::Save, 0, null, false, true, DeliveryDecisionEnum::Unverifiable];
    }
}
