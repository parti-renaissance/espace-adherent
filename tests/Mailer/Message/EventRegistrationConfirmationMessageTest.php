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
            'https://enmarche.code/evenements/foo-bar?test=foo&test2=bar'
        );

        self::assertMessage(
            EventRegistrationConfirmationMessage::class,
            [
                'event_name' => 'Événement #1',
                'event_organizer' => 'Jean Doe',
                'event_url' => 'https%3A%2F%2Fenmarche.code%2Fevenements%2Ffoo-bar%3Ftest%3Dfoo%26test2%3Dbar',
                'event_date' => '25/10/2017',
                'event_hour' => '17:30',
                'event_address' => '1, rue des alouettes',
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
                'event_name' => 'Événement #1',
                'event_organizer' => 'Jean Doe',
                'event_url' => 'https%3A%2F%2Fenmarche.code%2Fevenements%2Ffoo-bar%3Ftest%3Dfoo%26test2%3Dbar',
                'event_date' => '25/10/2017',
                'event_hour' => '17:30',
                'event_address' => '1, rue des alouettes',
                'recipient_first_name' => 'Bernard',
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
        $event
            ->expects(self::exactly(2))
            ->method('getBeginAt')
            ->willReturn(new \DateTime('2017-10-25 17:30:00'))
        ;
        $event
            ->expects(self::once())
            ->method('getInlineFormattedAddress')
            ->willReturn('1, rue des alouettes')
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
