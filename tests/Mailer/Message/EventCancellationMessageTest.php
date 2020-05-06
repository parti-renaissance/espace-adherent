<?php

namespace Tests\App\Mailer\Message;

use App\Entity\EventRegistration;
use App\Mailer\Message\EventCancellationMessage;
use App\Mailer\Message\EventNotificationMessage;
use App\Mailer\Message\MessageRecipient;

class EventCancellationMessageTest extends AbstractEventMessageTest
{
    const SEARCH_EVENTS_URL = 'https://test.enmarche.code/evenements';

    public function testCreateEventCancellationMessage()
    {
        $recipients[] = $this->createRegistrationMock('jb@example.com', 'Jean', 'Berenger');
        $recipients[] = $this->createRegistrationMock('ml@example.com', 'Marie', 'Lambert');
        $recipients[] = $this->createRegistrationMock('ez@example.com', 'Éric', 'Zitrone');

        $message = EventCancellationMessage::create(
            $recipients,
            $this->createAdherentMock('em@example.com', 'Émmanuel', 'Macron'),
            $this->createEventMock('En Marche Lyon', '2017-02-01 15:30:00', '15 allées Paul Bocuse', '69006-69386'),
            self::SEARCH_EVENTS_URL,
            function (EventRegistration $registration) {
                return EventNotificationMessage::getRecipientVars($registration->getFirstName());
            }
        );

        $this->assertSame('event-cancellation', $message->generateTemplateName());
        $this->assertCount(3, $message->getRecipients());
        $this->assertSame('L\'événement "En Marche Lyon" a été annulé.', $message->getSubject());
        $this->assertCount(2, $message->getVars());
        $this->assertSame(
            [
                'event_name' => 'En Marche Lyon',
                'event_slug' => self::SEARCH_EVENTS_URL,
            ],
            $message->getVars()
        );

        $recipient = $message->getRecipient(0);
        $this->assertInstanceOf(MessageRecipient::class, $recipient);
        $this->assertSame('jb@example.com', $recipient->getEmailAddress());
        $this->assertSame('Jean Berenger', $recipient->getFullName());
        $this->assertSame(['target_firstname' => 'Jean'], $recipient->getVars());

        $recipient = $message->getRecipient(2);
        $this->assertInstanceOf(MessageRecipient::class, $recipient);
        $this->assertSame('ez@example.com', $recipient->getEmailAddress());
        $this->assertSame('Éric Zitrone', $recipient->getFullName());
        $this->assertSame(['target_firstname' => 'Éric'], $recipient->getVars());
    }
}
