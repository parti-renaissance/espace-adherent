<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\Event;
use AppBundle\Mailer\Message\EventNotificationMessage;

/**
 * @group message
 */
class EventNotificationMessageTest extends MessageTestCase
{
    /**
     * @var Event|null
     */
    private $event;

    public function testCreate(): void
    {
        $message = EventNotificationMessage::create(
            [
                $this->createAdherent('jean@example.com', 'Jean', 'Doe'),
                $this->createAdherent('bernard@example.com', 'Bernard', 'Smith'),
            ],
            $this->createAdherent('host@example.com', 'Animateur', 'Jones'),
            $this->event,
            'https://enmarche.code/evenement/foo-bar',
            'https://enmarche.code/evenement/foo-bar/participer'
        );

        self::assertMessage(
            EventNotificationMessage::class,
            [
                'host_first_name' => 'Animateur',
                'event_name' => 'Événement #1',
                'event_date' => 'jeudi 1 mars 2018',
                'event_hour' => '09h30',
                'event_address' => '1 rue Montaigne, 06200 Nice',
                'event_show_link' => 'https://enmarche.code/evenement/foo-bar',
                'event_attend_link' => 'https://enmarche.code/evenement/foo-bar/participer',
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
                'host_first_name' => 'Animateur',
                'event_name' => 'Événement #1',
                'event_date' => 'jeudi 1 mars 2018',
                'event_hour' => '09h30',
                'event_address' => '1 rue Montaigne, 06200 Nice',
                'event_show_link' => 'https://enmarche.code/evenement/foo-bar',
                'event_attend_link' => 'https://enmarche.code/evenement/foo-bar/participer',
                'first_name' => 'Jean',
            ],
            $message
        );
        self::assertMessageRecipient(
            'bernard@example.com',
            'Bernard Smith',
            [
                'host_first_name' => 'Animateur',
                'event_name' => 'Événement #1',
                'event_date' => 'jeudi 1 mars 2018',
                'event_hour' => '09h30',
                'event_address' => '1 rue Montaigne, 06200 Nice',
                'event_show_link' => 'https://enmarche.code/evenement/foo-bar',
                'event_attend_link' => 'https://enmarche.code/evenement/foo-bar/participer',
                'first_name' => 'Bernard',
            ],
            $message
        );

        self::assertNoCC($message);
    }

    protected function setUp()
    {
        $this->event = $this->createMock(Event::class);

        $this->event
            ->expects(self::once())
            ->method('getName')
            ->willReturn('Événement #1')
        ;
        $this->event
            ->expects(self::exactly(3))
            ->method('getBeginAt')
            ->willReturn(new \DateTime('2018-03-01 09:30:00'))
        ;
        $this->event
            ->expects(self::once())
            ->method('getInlineFormattedAddress')
            ->willReturn('1 rue Montaigne, 06200 Nice')
        ;
    }

    protected function tearDown()
    {
        $this->event = null;
    }
}
