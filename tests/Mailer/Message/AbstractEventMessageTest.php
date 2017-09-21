<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\BaseEvent;
use AppBundle\Entity\CitizenInitiative;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeFeedItem;
use AppBundle\Entity\Event;
use AppBundle\Entity\PostAddress;
use PHPUnit\Framework\TestCase;

abstract class AbstractEventMessageTest extends TestCase
{
    protected function createEventMock(string $name, string $beginAt, string $street, string $cityCode, ?string $committeeName = null): Event
    {
        $address = PostAddress::createFrenchAddress($street, $cityCode)->getInlineFormattedAddress('fr_FR');

        $event = $this->createMock(Event::class);
        $event->expects(static::any())->method('getName')->willReturn($name);
        $event->expects(static::any())->method('getBeginAt')->willReturn(new \DateTime($beginAt));
        $event->expects(static::any())->method('getInlineFormattedAddress')->with('fr_FR')->willReturn($address);

        if ($committeeName) {
            $committee = $this->createMock(Committee::class);
            $committee->expects(static::any())->method('getName')->willReturn($committeeName);

            $event->expects(static::any())->method('getCommittee')->willReturn($committee);
        }

        return $event;
    }

    protected function createCitizenInitiativeMock(string $name, string $beginAt, string $street, string $cityCode, string $slug = '', ?Adherent $organizer = null): CitizenInitiative
    {
        $address = PostAddress::createFrenchAddress($street, $cityCode)->getInlineFormattedAddress('fr_FR');

        $citizenInitiative = $this->createMock(CitizenInitiative::class);
        $citizenInitiative->expects(static::any())->method('getName')->willReturn($name);
        $citizenInitiative->expects(static::any())->method('getBeginAt')->willReturn(new \DateTime($beginAt));
        $citizenInitiative->expects(static::any())->method('getInlineFormattedAddress')->with('fr_FR')->willReturn($address);
        $citizenInitiative->expects(static::any())->method('getSlug')->willReturn($slug);

        if ($organizer) {
            $citizenInitiative->expects(static::any())->method('getOrganizer')->willReturn($organizer);
        }

        return $citizenInitiative;
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

    protected function createCommitteeFeedItemMock(Adherent $author, string $content, ?BaseEvent $event = null, ?string $committeeName = null): CommitteeFeedItem
    {
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
