<?php

declare(strict_types=1);

namespace Tests\App\Unit\Ses\Webhook;

use App\Ses\Webhook\SesFeedbackType;
use App\Ses\Webhook\SesNotificationParser;
use PHPUnit\Framework\TestCase;

class SesNotificationParserTest extends TestCase
{
    private SesNotificationParser $parser;

    protected function setUp(): void
    {
        $this->parser = new SesNotificationParser();
    }

    public function testParsesPermanentBounceEventPublishingFormat(): void
    {
        $event = $this->parser->parse($this->sns([
            'eventType' => 'Bounce',
            'bounce' => [
                'bounceType' => 'Permanent',
                'bouncedRecipients' => [['emailAddress' => 'dead@example.org']],
            ],
        ]));

        self::assertNotNull($event);
        self::assertSame(SesFeedbackType::HARD_BOUNCE, $event->type);
        self::assertSame(['dead@example.org'], $event->recipients);
    }

    public function testParsesPermanentBounceNotificationFormat(): void
    {
        $event = $this->parser->parse($this->sns([
            'notificationType' => 'Bounce',
            'bounce' => [
                'bounceType' => 'Permanent',
                'bouncedRecipients' => [['emailAddress' => 'dead@example.org']],
            ],
        ]));

        self::assertNotNull($event);
        self::assertSame(SesFeedbackType::HARD_BOUNCE, $event->type);
    }

    public function testIgnoresTransientBounce(): void
    {
        self::assertNull($this->parser->parse($this->sns([
            'eventType' => 'Bounce',
            'bounce' => [
                'bounceType' => 'Transient',
                'bouncedRecipients' => [['emailAddress' => 'full-mailbox@example.org']],
            ],
        ])));
    }

    public function testIgnoresUndeterminedBounce(): void
    {
        self::assertNull($this->parser->parse($this->sns([
            'eventType' => 'Bounce',
            'bounce' => [
                'bounceType' => 'Undetermined',
                'bouncedRecipients' => [['emailAddress' => 'maybe@example.org']],
            ],
        ])));
    }

    public function testParsesComplaint(): void
    {
        $event = $this->parser->parse($this->sns([
            'eventType' => 'Complaint',
            'complaint' => [
                'complainedRecipients' => [['emailAddress' => 'angry@example.org']],
            ],
        ]));

        self::assertNotNull($event);
        self::assertSame(SesFeedbackType::COMPLAINT, $event->type);
        self::assertSame(['angry@example.org'], $event->recipients);
    }

    public function testExtractsAllRecipients(): void
    {
        $event = $this->parser->parse($this->sns([
            'eventType' => 'Bounce',
            'bounce' => [
                'bounceType' => 'Permanent',
                'bouncedRecipients' => [
                    ['emailAddress' => 'a@example.org'],
                    ['emailAddress' => 'b@example.org'],
                ],
            ],
        ]));

        self::assertNotNull($event);
        self::assertSame(['a@example.org', 'b@example.org'], $event->recipients);
    }

    public function testIgnoresDelivery(): void
    {
        self::assertNull($this->parser->parse($this->sns(['eventType' => 'Delivery'])));
    }

    public function testIgnoresUnknownType(): void
    {
        self::assertNull($this->parser->parse($this->sns(['eventType' => 'Open'])));
    }

    public function testReturnsNullWhenMessageIsNotJson(): void
    {
        self::assertNull($this->parser->parse(['MessageId' => 'sns-1', 'Message' => 'not-json']));
    }

    public function testReturnsNullWhenMessageMissing(): void
    {
        self::assertNull($this->parser->parse(['MessageId' => 'sns-1']));
    }

    public function testPermanentBounceWithoutRecipientsIsTreatedAsDrift(): void
    {
        $payload = $this->sns([
            'eventType' => 'Bounce',
            'bounce' => ['bounceType' => 'Permanent', 'bouncedRecipients' => []],
        ]);

        // Actionable feedback but no usable recipient: parse() returns null while describesFeedback() is
        // true, so the handler can log a format-drift error instead of silently swallowing it.
        self::assertNull($this->parser->parse($payload));
        self::assertTrue($this->parser->describesFeedback($payload));
    }

    public function testDescribesFeedbackTrueForPermanentBounceAndComplaint(): void
    {
        self::assertTrue($this->parser->describesFeedback($this->sns([
            'eventType' => 'Bounce',
            'bounce' => ['bounceType' => 'Permanent', 'bouncedRecipients' => [['emailAddress' => 'x@example.org']]],
        ])));
        self::assertTrue($this->parser->describesFeedback($this->sns([
            'eventType' => 'Complaint',
            'complaint' => ['complainedRecipients' => [['emailAddress' => 'x@example.org']]],
        ])));
    }

    public function testDescribesFeedbackFalseForTransientBounceAndDelivery(): void
    {
        self::assertFalse($this->parser->describesFeedback($this->sns([
            'eventType' => 'Bounce',
            'bounce' => ['bounceType' => 'Transient', 'bouncedRecipients' => [['emailAddress' => 'x@example.org']]],
        ])));
        self::assertFalse($this->parser->describesFeedback($this->sns(['eventType' => 'Delivery'])));
    }

    private function sns(array $sesEvent): array
    {
        return ['MessageId' => 'sns-1', 'Message' => json_encode($sesEvent)];
    }
}
