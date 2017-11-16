<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Mailer\Message\EventCancellationMessage;
use AppBundle\Mailer\Message\EventNotificationMessage;
use AppBundle\Mailer\Message\MessageRecipient;

class EventCancellationMessageTest extends AbstractEventMessageTest
{
    const SEARCH_EVENTS_URL = 'https://enmarche.dev/evenements';

    public function testCreateEventCancellationMessage()
    {
        $recipients[] = $this->createAdherentMock('em@example.com', 'Émmanuel', 'Macron');
        $recipients[] = $this->createAdherentMock('jb@example.com', 'Jean', 'Berenger');
        $recipients[] = $this->createAdherentMock('ml@example.com', 'Marie', 'Lambert');
        $recipients[] = $this->createAdherentMock('ez@example.com', 'Éric', 'Zitrone');

        $message = EventCancellationMessage::create(
            $recipients,
            $recipients[0],
            $this->createEventMock('En Marche Lyon', '2017-02-01 15:30:00', '15 allées Paul Bocuse', '69006-69386'),
            self::SEARCH_EVENTS_URL,
            function (Adherent $adherent) {
                return EventNotificationMessage::getRecipientVars($adherent->getFirstName());
            }
        );

        $this->assertInstanceOf(EventCancellationMessage::class, $message);
        $this->assertSame('78678', $message->getTemplate());
        $this->assertCount(4, $message->getRecipients());
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
        $this->assertSame('em@example.com', $recipient->getEmailAddress());
        $this->assertSame('Émmanuel Macron', $recipient->getFullName());
        $this->assertSame(
            [
                'event_name' => 'En Marche Lyon',
                'event_slug' => self::SEARCH_EVENTS_URL,
                'target_firstname' => 'Émmanuel',
            ],
            $recipient->getVars()
        );

        $recipient = $message->getRecipient(3);
        $this->assertInstanceOf(MessageRecipient::class, $recipient);
        $this->assertSame('ez@example.com', $recipient->getEmailAddress());
        $this->assertSame('Éric Zitrone', $recipient->getFullName());
        $this->assertSame(
            [
                'event_name' => 'En Marche Lyon',
                'event_slug' => self::SEARCH_EVENTS_URL,
                'target_firstname' => 'Éric',
            ],
            $recipient->getVars()
        );
    }
}
