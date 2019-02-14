<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\BaseEvent;
use AppBundle\Entity\CitizenAction;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeFeedItem;
use AppBundle\Entity\Event;
use AppBundle\Entity\EventRegistration;
use AppBundle\Entity\PostAddress;
use PHPUnit\Framework\TestCase;

abstract class AbstractEventMessageTest extends TestCase
{
    protected function createEventMock(
        string $name,
        string $beginAt,
        string $street,
        string $cityCode,
        ?string $committeeName = null,
        string $timeZone = 'Europe/Paris'
    ): Event {
        $address = PostAddress::createFrenchAddress($street, $cityCode)->getInlineFormattedAddress('fr_FR');

        $event = $this->createMock(Event::class);
        $event->expects(static::any())->method('getName')->willReturn($name);
        $event->expects(static::any())->method('getBeginAt')->willReturn(new \DateTime($beginAt));
        $event->expects(static::any())->method('getTimeZone')->willReturn($timeZone);
        $localeBeginAt = new \DateTime($beginAt);
        $event->expects(static::any())->method('getLocalBeginAt')->willReturn($localeBeginAt->setTimezone(new \DateTimeZone($timeZone)));
        $event->expects(static::any())->method('getInlineFormattedAddress')->with('fr_FR')->willReturn($address);

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

    protected function createCitizenActionMock(
        string $name,
        string $beginAt,
        string $street,
        string $cityCode,
        string $timeZone = 'Europe/Paris'
    ): CitizenAction {
        $citizenAction = $this->getMockBuilder(CitizenAction::class)->disableOriginalConstructor()->getMock();
        $address = PostAddress::createFrenchAddress($street, $cityCode)->getInlineFormattedAddress('fr_FR');

        $citizenAction->expects(static::any())->method('getName')->willReturn($name);
        $citizenAction->expects(static::any())->method('getBeginAt')->willReturn(new \DateTime($beginAt));
        $citizenAction->expects(static::any())->method('getTimeZone')->willReturn($timeZone);
        $localeBeginAt = new \DateTime($beginAt);
        $citizenAction->expects(static::any())->method('getLocalBeginAt')->willReturn($localeBeginAt->setTimezone(new \DateTimeZone($timeZone)));
        $citizenAction->expects(static::any())->method('getInlineFormattedAddress')->with('fr_FR')->willReturn($address);

        return $citizenAction;
    }
}
