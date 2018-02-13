<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\EventInvite;
use AppBundle\Mailer\Message\EventInvitationMessage;
use AppBundle\Mailer\Message\Message;

class EventInvitationMessageTest extends AbstractEventMessageTest
{
    const SHOW_EVENT_URL = 'https://enmarche.dev/comites/59b1314d-dcfb-4a4c-83e1-212841d0bd0f/evenements/2017-01-31-en-marche-lyon';

    public function testCreateFromInvite()
    {
        $guests[] = 'em@example.com';
        $guests[] = 'jb@example.com';
        $guests[] = 'ml@example.com';
        $guests[] = 'ez@example.com';

        $event = $this->createEventMock('En Marche Lyon', '2017-02-01 15:30:00', '15 allées Paul Bocuse', '69006-69386', 'EM Lyon');

        $eventInvite = $this->createMock(EventInvite::class);
        $eventInvite->expects(static::any())->method('getEmail')->willReturn('em@example.com');
        $eventInvite->expects(static::any())->method('getFullName')->willReturn('Émmanuel Macron');
        $eventInvite->expects(static::any())->method('getFirstName')->willReturn('Émmanuel');
        $eventInvite->expects(static::any())->method('getMessage')->willReturn('Rendez-vous à Lyon.');
        $eventInvite->expects(static::any())->method('getGuests')->willReturn($guests);

        $message = EventInvitationMessage::createFromInvite(
            $eventInvite,
            $event,
            self::SHOW_EVENT_URL
        );

        $this->assertInstanceOf(EventInvitationMessage::class, $message);
        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame('em@example.com', $message->getReplyTo());
        $this->assertCount(4, $message->getVars());
        $this->assertSame(
            [
                'sender_firstname' => 'Émmanuel',
                'sender_message' => 'Rendez-vous à Lyon.',
                'event_name' => 'En Marche Lyon',
                'event_slug' => self::SHOW_EVENT_URL,
            ],
            $message->getVars()
        );
        $this->assertCount(4, $message->getCC());

        $recipients = $message->getCC();

        foreach ($recipients as $key => $recipient) {
            $this->assertSame($guests[$key], $recipient);
        }
    }
}
