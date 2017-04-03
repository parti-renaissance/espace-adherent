<?php

namespace Tests\AppBundle\Mailjet\Message;

use AppBundle\Entity\EventRegistration;
use AppBundle\Mailjet\Message\EventRegistrationConfirmationMessage;
use Ramsey\Uuid\UuidInterface;

class EventRegistrationConfirmationMessageTest extends AbstractEventMessageTest
{
    public function testCreateMessageFromEventRegistration()
    {
        $event = $this->createEventMock('Grand Meeting de Paris', '2017-02-01 15:30:00', 'Palais des Congrés, Porte Maillot', '75001-75101', 'EM Paris');
        $event->expects($this->any())->method('getOrganizerName')->willReturn('Michelle');

        $registration = $this->createMock(EventRegistration::class);
        $registration->expects($this->any())->method('getEvent')->willReturn($event);
        $registration->expects($this->any())->method('getFirstName')->willReturn('John');
        $registration->expects($this->any())->method('getEmailAddress')->willReturn('john@bar.com');

        $message = EventRegistrationConfirmationMessage::createFromRegistration($registration);

        $this->assertInstanceOf(EventRegistrationConfirmationMessage::class, $message);
        $this->assertInstanceOf(UuidInterface::class, $message->getUuid());
        $this->assertSame('118620', $message->getTemplate());
        $this->assertSame('john@bar.com', $message->getRecipient(0)->getEmailAddress());
        $this->assertSame('John', $message->getRecipient(0)->getFullName());
        $this->assertSame('Confirmation de participation à un événement En Marche !', $message->getSubject());
        $this->assertSame(
            [
                'prenom' => 'John',
                'event_name' => 'Grand Meeting de Paris',
                'event_organiser' => 'Michelle',
            ],
            $message->getVars()
        );
    }
}
