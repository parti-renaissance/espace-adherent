<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\EventRegistration;
use AppBundle\Mailer\Message\EventRegistrationConfirmationMessage;
use AppBundle\Mailer\Message\Message;

class EventRegistrationConfirmationMessageTest extends AbstractEventMessageTest
{
    const EVENT_LINK = 'http://en-marche.fr/evenements/201/2017-12-27-evenement-a-lyon';

    public function testCreateFromRegistration()
    {
        $event = $this->createEventMock('Grand Meeting de Paris', '2017-02-01 15:30:00', 'Palais des Congrés, Porte Maillot', '75001-75101', 'EM Paris');
        $event->expects($this->any())->method('getOrganizerName')->willReturn('Michelle');

        $registration = $this->createMock(EventRegistration::class);
        $registration->expects($this->any())->method('getEvent')->willReturn($event);
        $registration->expects($this->any())->method('getFirstName')->willReturn('John');
        $registration->expects($this->any())->method('getEmailAddress')->willReturn('john@bar.com');

        $message = EventRegistrationConfirmationMessage::createFromRegistration($registration, self::EVENT_LINK);

        $this->assertInstanceOf(EventRegistrationConfirmationMessage::class, $message);
        $this->assertInstanceOf(Message::class, $message);
        $this->assertCount(6, $message->getVars());
        $this->assertSame(
            [
                'event_name' => 'Grand Meeting de Paris',
                'event_date' => 'mercredi 1 février 2017',
                'event_hour' => '15h30',
                'event_address' => 'Palais des Congrés Porte Maillot, 75001 Paris 1er',
                'event_organizer' => 'Michelle',
                'event_link' => self::EVENT_LINK,
            ],
            $message->getVars()
        );
        $this->assertCount(1, $message->getRecipients());

        $recipient = $message->getRecipient(0);
        $this->assertSame('john@bar.com', $recipient->getEmailAddress());
        $this->assertSame('John', $recipient->getFullName());
        $this->assertSame(
            [
                'event_name' => 'Grand Meeting de Paris',
                'event_date' => 'mercredi 1 février 2017',
                'event_hour' => '15h30',
                'event_address' => 'Palais des Congrés Porte Maillot, 75001 Paris 1er',
                'event_organizer' => 'Michelle',
                'event_link' => self::EVENT_LINK,
                'prenom' => 'John',
            ],
            $recipient->getVars()
        );
    }
}
