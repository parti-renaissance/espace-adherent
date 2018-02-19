<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\Event;
use AppBundle\Entity\EventInvite;
use AppBundle\Mailer\Message\EventInvitationMessage;

/**
 * @group message
 */
class EventInvitationMessageTest extends MessageTestCase
{
    /**
     * @var EventInvite|null
     */
    private $eventInvitation;

    /**
     * @var Event|null
     */
    private $event;

    public function testCreate(): void
    {
        $message = EventInvitationMessage::create(
            $this->eventInvitation,
            $this->event,
            'https://enmarche.code/evenement/foo-bar'
        );

        self::assertMessage(
            EventInvitationMessage::class,
            [
                'sender_first_name' => 'Jean',
                'sender_full_name' => 'Jean Doe',
                'sender_message' => 'Contenu du message de test.',
                'event_name' => 'Événement #1',
                'event_slug' => 'https://enmarche.code/evenement/foo-bar',
            ],
            $message
        );

        self::assertNoSender($message);
        self::assertReplyTo('jean@example.com', $message);

        self::assertCountRecipients(1, $message);

        self::assertMessageRecipient(
            'jean@example.com',
            'Jean Doe',
            [
                'sender_first_name' => 'Jean',
                'sender_full_name' => 'Jean Doe',
                'sender_message' => 'Contenu du message de test.',
                'event_name' => 'Événement #1',
                'event_slug' => 'https://enmarche.code/evenement/foo-bar',
            ],
            $message
        );

        self::assertCountCC(2, $message);
        self::assertMessageCC('foo@example.com', $message);
        self::assertMessageCC('bar@example.com', $message);
    }

    protected function setUp()
    {
        $this->eventInvitation = $this->createMock(EventInvite::class);

        $this->eventInvitation
            ->expects(self::exactly(2))
            ->method('getEmail')
            ->willReturn('jean@example.com')
        ;
        $this->eventInvitation
            ->expects(self::exactly(2))
            ->method('getFullName')
            ->willReturn('Jean Doe')
        ;
        $this->eventInvitation
            ->expects(self::once())
            ->method('getFirstName')
            ->willReturn('Jean')
        ;
        $this->eventInvitation
            ->expects(self::once())
            ->method('getMessage')
            ->willReturn('Contenu du message de test.')
        ;
        $this->eventInvitation
            ->expects(self::once())
            ->method('getGuests')
            ->willReturn(['foo@example.com', 'bar@example.com'])
        ;

        $this->event = $this->createMock(Event::class);

        $this->event
            ->expects(self::once())
            ->method('getName')
            ->willReturn('Événement #1')
        ;
    }

    protected function tearDown()
    {
        $this->eventInvitation = null;
        $this->event = null;
    }
}
