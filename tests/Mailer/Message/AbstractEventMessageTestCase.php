<?php

namespace Tests\App\Mailer\Message;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeFeedItem;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\CommitteeEvent;
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
        string $description = ''
    ): CommitteeEvent {
        $address = $this->createPostAddress($street, $cityCode)->getInlineFormattedAddress('fr_FR');

        $event = $this->createMock(CommitteeEvent::class);
        $event->expects(static::any())->method('getName')->willReturn($name);
        $event->expects(static::any())->method('getBeginAt')->willReturn(new \DateTime($beginAt));
        $event->expects(static::any())->method('getTimeZone')->willReturn($timeZone);
        $localeBeginAt = new \DateTime($beginAt);
        $event->expects(static::any())->method('getLocalBeginAt')->willReturn($localeBeginAt->setTimezone(new \DateTimeZone($timeZone)));
        $event->expects(static::any())->method('getInlineFormattedAddress')->with('fr_FR')->willReturn($address);
        $event->expects(static::any())->method('getDescription')->willReturn($description);

        if ($committeeName) {
            $committee = $this->createMock(Committee::class);
            $committee->expects(static::any())->method('getName')->willReturn($committeeName);

            $event->expects(static::any())->method('getCommittee')->willReturn($committee);
        }

        return $event;
    }

    protected function createRegistrationMock(
        string $emailAddress,
        string $firstName,
        string $lastName
    ): EventRegistration {
        $registration = $this->createMock(EventRegistration::class);
        $registration->expects(static::any())->method('getEmailAddress')->willReturn($emailAddress);
        $registration->expects(static::any())->method('getFirstName')->willReturn($firstName);
        $registration->expects(static::any())->method('getLastName')->willReturn($lastName);

        return $registration;
    }

    protected function createAdherentMock(string $emailAddress, string $firstName, string $lastName): Adherent
    {
        $adherent = $this->createMock(Adherent::class);
        $adherent->expects(static::any())->method('getEmailAddress')->willReturn($emailAddress);
        $adherent->expects(static::any())->method('getFirstName')->willReturn($firstName);
        $adherent->expects(static::any())->method('getLastName')->willReturn($lastName);
        $adherent->expects(static::any())->method('getFullName')->willReturn($firstName.' '.$lastName);

        return $adherent;
    }

    protected function createCommitteeFeedItemMock(
        Adherent $author,
        string $content,
        ?BaseEvent $event = null,
        ?string $committeeName = null
    ): CommitteeFeedItem {
        $mock = $this->getMockBuilder(CommitteeFeedItem::class)->disableOriginalConstructor()->getMock();
        $mock->expects($this->any())->method('getAuthor')->willReturn($author);
        $mock->expects($this->any())->method('getAuthorFirstName')->willReturn($author->getFirstName());
        $mock->expects($this->any())->method('getContent')->willReturn($content);

        if ($event) {
            $mock->expects($this->any())->method('getEvent')->willReturn($event);
        }

        if ($committeeName) {
            $committee = $this->createMock(Committee::class);
            $committee->expects(static::any())->method('getName')->willReturn($committeeName);

            $mock->expects(static::any())->method('getCommittee')->willReturn($committee);
        }

        return $mock;
    }
}
