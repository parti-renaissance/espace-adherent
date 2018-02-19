<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\BaseEvent;
use AppBundle\Mailer\Message\EventCancellationMessage;

/**
 * @group message
 */
class EventCancellationMessageTest extends MessageTestCase
{
    /**
     * @var BaseEvent|null
     */
    private $event;

    public function testCreate(): void
    {
        $message = EventCancellationMessage::create(
            [
                $this->createEventRegistration('jean@example.com', 'Jean', 'Doe'),
                $this->createEventRegistration('bernard@example.com', 'Bernard', 'Smith'),
            ],
            $this->createAdherent('host@example.com', 'Animateur', 'Jones'),
            $this->event,
            'https://enmarche.code/evenements'
        );

        self::assertMessage(
            EventCancellationMessage::class,
            [
                'event_name' => 'Événement #1',
                'event_url' => 'https://enmarche.code/evenements',
            ],
            $message
        );

        self::assertNoSender($message);
        self::assertReplyTo('host@example.com', $message);

        self::assertCountRecipients(2, $message);

        self::assertMessageRecipient(
            'jean@example.com',
            'Jean Doe',
            [
                'event_name' => 'Événement #1',
                'event_url' => 'https://enmarche.code/evenements',
                'first_name' => 'Jean',
            ],
            $message
        );
        self::assertMessageRecipient(
            'bernard@example.com',
            'Bernard Smith',
            [
                'event_name' => 'Événement #1',
                'event_url' => 'https://enmarche.code/evenements',
                'first_name' => 'Bernard',
            ],
            $message
        );

        self::assertNoCC($message);
    }

    protected function setUp()
    {
        $this->event = $this->createMock(BaseEvent::class);

        $this->event
            ->expects(self::once())
            ->method('getName')
            ->willReturn('Événement #1')
        ;
    }

    protected function tearDown()
    {
        $this->event = null;
    }
}
