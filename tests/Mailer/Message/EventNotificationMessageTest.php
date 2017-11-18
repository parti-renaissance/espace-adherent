<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Mailer\Message\EventNotificationMessage;
use AppBundle\Mailer\Message\Message;
use AppBundle\Mailer\Message\MessageRecipient;

class EventNotificationMessageTest extends AbstractEventMessageTest
{
    private const SHOW_EVENT_URL = 'https://test.enmarche.code/comites/59b1314d-dcfb-4a4c-83e1-212841d0bd0f/evenements/2017-01-31-en-marche-lyon';

    public function testCreate()
    {
        $recipients[] = $this->createAdherentMock('em@example.com', 'Émmanuel', 'Macron');
        $recipients[] = $this->createAdherentMock('jb@example.com', 'Jean', 'Berenger');
        $recipients[] = $this->createAdherentMock('ml@example.com', 'Marie', 'Lambert');
        $recipients[] = $this->createAdherentMock('ez@example.com', 'Éric', 'Zitrone');

        $event = $this->createEventMock('En Marche Lyon', '2017-02-01 15:30:00', '15 allées Paul Bocuse', '69006-69386', 'EM Lyon');
        $event->expects($this->once())->method('getDescription')->willReturn('En Marche à Lyon');

        $message = EventNotificationMessage::create(
            $recipients,
            $recipients[0],
            $event,
            self::SHOW_EVENT_URL
        );

        $this->assertInstanceOf(EventNotificationMessage::class, $message);
        $this->assertInstanceOf(Message::class, $message);
        $this->assertCount(7, $message->getVars());
        $this->assertSame('em@example.com', $message->getReplyTo());
        $this->assertSame(
            [
                'animator_firstname' => 'Émmanuel',
                'event_name' => 'En Marche Lyon',
                'event_description' => 'En Marche à Lyon',
                'event_date' => 'mercredi 1 février 2017',
                'event_hour' => '15h30',
                'event_address' => '15 allées Paul Bocuse, 69006 Lyon 6e',
                'event_slug' => self::SHOW_EVENT_URL,
            ],
            $message->getVars()
        );
        $this->assertCount(4, $message->getRecipients());

        $recipient = $message->getRecipient(0);
        $this->assertInstanceOf(MessageRecipient::class, $recipient);
        $this->assertSame('em@example.com', $recipient->getEmailAddress());
        $this->assertSame('Émmanuel Macron', $recipient->getFullName());
        $this->assertSame(
            [
                'animator_firstname' => 'Émmanuel',
                'event_name' => 'En Marche Lyon',
                'event_description' => 'En Marche à Lyon',
                'event_date' => 'mercredi 1 février 2017',
                'event_hour' => '15h30',
                'event_address' => '15 allées Paul Bocuse, 69006 Lyon 6e',
                'event_slug' => self::SHOW_EVENT_URL,
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
                'animator_firstname' => 'Émmanuel',
                'event_name' => 'En Marche Lyon',
                'event_description' => 'En Marche à Lyon',
                'event_date' => 'mercredi 1 février 2017',
                'event_hour' => '15h30',
                'event_address' => '15 allées Paul Bocuse, 69006 Lyon 6e',
                'event_slug' => self::SHOW_EVENT_URL,
                'target_firstname' => 'Éric',
            ],
            $recipient->getVars()
        );
    }
}
