<?php

declare(strict_types=1);

namespace Tests\App\Unit\Entity\Email;

use App\Entity\Email\EmailLog;
use PHPUnit\Framework\TestCase;
use Tests\App\Test\Mailer\Message\DummyMessage;

class EmailLogTest extends TestCase
{
    public function testCreateFromMessageUsesResolvedEmailSenderName(): void
    {
        // The sender resolved on the built email (e.g. a template's EmailSender) must win over the
        // message-level sender, so the log reflects who the email is actually sent from.
        $log = EmailLog::createFromMessage(DummyMessage::create(), '{}', true, 'Sender from DB');

        self::assertSame('Sender from DB', $log->getSender());
    }

    public function testCreateFromMessageFallsBackToMessageSenderName(): void
    {
        $message = DummyMessage::create();
        $message->setSenderName('Message sender');

        $log = EmailLog::createFromMessage($message, '{}', true);

        self::assertSame('Message sender', $log->getSender());
    }

    public function testCreateFromMessageFallsBackToRenaissanceWhenNoSender(): void
    {
        $log = EmailLog::createFromMessage(DummyMessage::create(), '{}', true);

        self::assertSame('Renaissance', $log->getSender());
    }
}
