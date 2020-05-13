<?php

namespace Tests\App\Mailer\Message;

use App\Entity\EventRegistration;
use App\Mailer\Message\EventRegistrationConfirmationMessage;

class EventRegistrationConfirmationMessageTest extends AbstractEventMessageTest
{
    const EVENT_LINK = 'http://en-marche.fr/evenements/201/2017-12-27-evenement-a-lyon';

    public function testCreateMessageFromEventRegistration()
    {
        $event = $this->createEventMock('Grand Meeting de Paris', '2017-02-01 15:30:00', 'Palais des Congrés, Porte Maillot', '75001-75101', 'EM Paris');
        $event->expects($this->any())->method('getOrganizerName')->willReturn('Michelle');

        $registration = $this->createMock(EventRegistration::class);
        $registration->expects($this->any())->method('getEvent')->willReturn($event);
        $registration->expects($this->any())->method('getFirstName')->willReturn('John');
        $registration->expects($this->any())->method('getEmailAddress')->willReturn('john@bar.com');

        $message = EventRegistrationConfirmationMessage::createFromRegistration($registration, self::EVENT_LINK);

        $this->assertSame('event-registration-confirmation', $message->generateTemplateName());
        $this->assertSame('john@bar.com', $message->getRecipient(0)->getEmailAddress());
        $this->assertSame('John', $message->getRecipient(0)->getFullName());
        $this->assertSame('Confirmation de participation à un événement En Marche !', $message->getSubject());
        $this->assertSame(
            [
                'event_name' => 'Grand Meeting de Paris',
                'event_organiser' => 'Michelle',
                'event_link' => self::EVENT_LINK,
            ],
            $message->getVars()
        );

        $recipient = $message->getRecipient(0);

        $this->assertSame(['prenom' => 'John'], $recipient->getVars());
    }
}
