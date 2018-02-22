<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\BaseEvent;
use AppBundle\Entity\EventRegistration;
use AppBundle\Mailer\Message\EventRegistrationConfirmationMessage;

/**
 * @group message
 */
class EventRegistrationConfirmationMessageTest extends MessageTestCase
{
    /**
     * @var EventRegistration|null
     */
    private $eventRegistration;

    public function testCreate(): void
    {
        $message = EventRegistrationConfirmationMessage::create(
            $this->eventRegistration,
            'https://enmarche.code/evenements/foo-bar'
        );

        self::assertMessage(
            EventRegistrationConfirmationMessage::class,
            [
                'first_name' => 'Bernard',
                'event_name' => 'Événement #1',
                'event_organizer' => 'Jean Doe',
                'event_link' => 'https://enmarche.code/evenements/foo-bar',
            ],
            $message
        );

        self::assertNoSender($message);
        self::assertNoReplyTo($message);

        self::assertCountRecipients(1, $message);

        self::assertMessageRecipient(
            'bernard@example.com',
            'Bernard Smith',
            [
                'first_name' => 'Bernard',
                'event_name' => 'Événement #1',
                'event_organizer' => 'Jean Doe',
                'event_link' => 'https://enmarche.code/evenements/foo-bar',
            ],
            $message
        );

        self::assertNoCC($message);
    }

    protected function setUp()
    {
        $event = $this->createMock(BaseEvent::class);

        $event
            ->expects(self::once())
            ->method('getName')
            ->willReturn('Événement #1')
        ;
        $event
            ->expects(self::once())
            ->method('getOrganizerName')
            ->willReturn('Jean Doe')
        ;

        $this->eventRegistration = $this->createEventRegistration('bernard@example.com', 'Bernard', 'Smith');

        $this->eventRegistration
            ->expects(self::once())
            ->method('getEvent')
            ->willReturn($event)
        ;
    }

    protected function tearDown()
    {
        $this->eventRegistration = null;
    }
}
