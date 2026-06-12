<?php

declare(strict_types=1);

namespace Tests\App\Mailer\Message;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\Event\Event;
use App\Entity\Event\EventRegistration;
use Tests\App\AbstractKernelTestCase;

abstract class AbstractEventMessageTestCase extends AbstractKernelTestCase
{
    protected function createEventMock(
        string $name,
        string $beginAt,
        string $street,
        string $cityCode,
        ?string $committeeName = null,
        string $timeZone = 'Europe/Paris',
        string $description = '',
    ): Event {
        $address = $this->createPostAddress($street, $cityCode)->getInlineFormattedAddress('fr_FR');

        $event = $this->createStub(Event::class);
        $event->method('getName')->willReturn($name);
        $event->method('getBeginAt')->willReturn(new \DateTime($beginAt));
        $event->method('getTimeZone')->willReturn($timeZone);
        $localeBeginAt = new \DateTime($beginAt);
        $event->method('getLocalBeginAt')->willReturn($localeBeginAt->setTimezone(new \DateTimeZone($timeZone)));
        $event->method('getInlineFormattedAddress')->willReturnMap([
            ['fr_FR', $address],
        ]);
        $event->method('getDescription')->willReturn($description);

        if ($committeeName) {
            $committee = $this->createStub(Committee::class);
            $committee->method('getName')->willReturn($committeeName);

            $event->method('getCommittee')->willReturn($committee);
        }

        return $event;
    }

    protected function createRegistrationMock(
        string $emailAddress,
        string $firstName,
        string $lastName,
    ): EventRegistration {
        $registration = $this->createStub(EventRegistration::class);
        $registration->method('getEmailAddress')->willReturn($emailAddress);
        $registration->method('getFirstName')->willReturn($firstName);
        $registration->method('getLastName')->willReturn($lastName);

        return $registration;
    }

    protected function createAdherentMock(string $emailAddress, string $firstName, string $lastName): Adherent
    {
        $adherent = $this->createStub(Adherent::class);
        $adherent->method('getEmailAddress')->willReturn($emailAddress);
        $adherent->method('getFirstName')->willReturn($firstName);
        $adherent->method('getLastName')->willReturn($lastName);
        $adherent->method('getFullName')->willReturn($firstName.' '.$lastName);

        return $adherent;
    }
}
