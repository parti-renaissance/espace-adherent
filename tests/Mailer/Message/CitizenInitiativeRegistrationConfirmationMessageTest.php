<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\EventRegistration;
use AppBundle\Mailer\Message\CitizenInitiativeRegistrationConfirmationMessage;
use Ramsey\Uuid\UuidInterface;

class CitizenInitiativeRegistrationConfirmationMessageTest extends AbstractEventMessageTest
{
    const CITIZEN_INITIATIVE_LINK = 'http://en-marche.fr/initiative-citoyenne/254/2017-12-27-initiative-citoyenne-a-lyon';

    public function testCreateMessageFromEventRegistration()
    {
        $organizer = $this->createAdherentMock('michelle.doe@example.com', 'Michelle', 'Doe');

        $event = $this->createEventMock('Grand Meeting de Paris', '2017-02-01 15:30:00', 'Palais des Congrés, Porte Maillot', '75001-75101', 'EM Paris');
        $event->expects($this->any())->method('getOrganizer')->willReturn($organizer);

        $registration = $this->createMock(EventRegistration::class);
        $registration->expects($this->any())->method('getEvent')->willReturn($event);
        $registration->expects($this->any())->method('getFirstName')->willReturn('John');
        $registration->expects($this->any())->method('getEmailAddress')->willReturn('john@bar.com');

        $message = CitizenInitiativeRegistrationConfirmationMessage::createFromRegistration($registration, self::CITIZEN_INITIATIVE_LINK);

        $this->assertInstanceOf(CitizenInitiativeRegistrationConfirmationMessage::class, $message);
        $this->assertInstanceOf(UuidInterface::class, $message->getUuid());
        $this->assertSame('212744', $message->getTemplate());
        $this->assertSame('john@bar.com', $message->getRecipient(0)->getEmailAddress());
        $this->assertSame('John', $message->getRecipient(0)->getFullName());
        $this->assertSame('Confirmation de participation à une initiative citoyenne En Marche !', $message->getSubject());
        $this->assertSame(
            [
                'IC_name' => 'Grand Meeting de Paris',
                'IC_organiser_firstname' => 'Michelle',
                'IC_organiser_lastname' => 'Doe',
                'IC_link' => self::CITIZEN_INITIATIVE_LINK,
            ],
            $message->getVars()
        );

        $recipient = $message->getRecipient(0);

        $this->assertSame(
            [
                'IC_name' => 'Grand Meeting de Paris',
                'IC_organiser_firstname' => 'Michelle',
                'IC_organiser_lastname' => 'Doe',
                'IC_link' => self::CITIZEN_INITIATIVE_LINK,
                'prenom' => 'John',
            ],
            $recipient->getVars()
        );
    }
}
