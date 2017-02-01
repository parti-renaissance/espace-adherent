<?php

namespace Tests\AppBundle\Mailjet\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CommitteeEvent;
use AppBundle\Entity\PostAddress;
use AppBundle\Mailjet\Message\CommitteeEventNotificationMessage;
use AppBundle\Mailjet\Message\MailjetMessageRecipient;

class CommitteeEventNotificationMessageTest extends \PHPUnit_Framework_TestCase
{
    const SHOW_EVENT_URL = 'https://localhost/comites/59b1314d-dcfb-4a4c-83e1-212841d0bd0f/evenements/2017-01-31-en-marche-lyon';
    const ACCEPT_EVENT_URL = 'https://localhost/comites/59b1314d-dcfb-4a4c-83e1-212841d0bd0f/evenements/2017-01-31-en-marche-lyon/accepter';
    const IGNORE_EVENT_URL = 'https://localhost/comites/59b1314d-dcfb-4a4c-83e1-212841d0bd0f/evenements/2017-01-31-en-marche-lyon/ignorer';

    public function testCreateCommitteeEventNotificationMessage()
    {
        $recipients[] = $this->createAdherentMock('em@example.com', 'Émmanuel', 'Macron');
        $recipients[] = $this->createAdherentMock('jb@example.com', 'Jean', 'Berenger');
        $recipients[] = $this->createAdherentMock('ml@example.com', 'Marie', 'Lambert');
        $recipients[] = $this->createAdherentMock('ez@example.com', 'Éric', 'Zitrone');

        $message = CommitteeEventNotificationMessage::create(
            $recipients,
            $recipients[0],
            $this->createCommitteeEventMock('En Marche Lyon', '2017-02-01 15:30:00', '15 allées Paul Bocuse', '69006-69386'),
            self::SHOW_EVENT_URL,
            function (Adherent $adherent) {
                return CommitteeEventNotificationMessage::getRecipientVars(
                    $adherent->getFirstName(),
                    self::ACCEPT_EVENT_URL,
                    self::IGNORE_EVENT_URL
                );
            }
        );

        $this->assertInstanceOf(CommitteeEventNotificationMessage::class, $message);
        $this->assertSame('61378', $message->getTemplate());
        $this->assertCount(4, $message->getRecipients());
        $this->assertSame('Nouvel événement dans votre comité En Marche !', $message->getSubject());
        $this->assertCount(10, $message->getVars());
        $this->assertSame(
            [
                'animator_firstname' => 'Émmanuel',
                'event_name' => 'En Marche Lyon',
                'event_date' => 'mercredi 1 février 2017',
                'event_hour' => '15h30',
                'event_address' => '15 allées Paul Bocuse, 69006 Lyon 6e Arrondissement',
                'event_slug' => self::SHOW_EVENT_URL,
                'event-slug' => self::SHOW_EVENT_URL,
                'target_firstname' => '',
                'event_ok_link' => '',
                'event_ko_link' => self::SHOW_EVENT_URL,
            ],
            $message->getVars()
        );

        $recipient = $message->getRecipient(0);
        $this->assertInstanceOf(MailjetMessageRecipient::class, $recipient);
        $this->assertSame('em@example.com', $recipient->getEmailAddress());
        $this->assertSame('Émmanuel Macron', $recipient->getFullName());
        $this->assertSame(
            [
                'animator_firstname' => 'Émmanuel',
                'event_name' => 'En Marche Lyon',
                'event_date' => 'mercredi 1 février 2017',
                'event_hour' => '15h30',
                'event_address' => '15 allées Paul Bocuse, 69006 Lyon 6e Arrondissement',
                'event_slug' => self::SHOW_EVENT_URL,
                'event-slug' => self::SHOW_EVENT_URL,
                'target_firstname' => 'Émmanuel',
                'event_ok_link' => self::ACCEPT_EVENT_URL,
                'event_ko_link' => self::IGNORE_EVENT_URL,
            ],
            $recipient->getVars()
        );

        $recipient = $message->getRecipient(3);
        $this->assertInstanceOf(MailjetMessageRecipient::class, $recipient);
        $this->assertSame('ez@example.com', $recipient->getEmailAddress());
        $this->assertSame('Éric Zitrone', $recipient->getFullName());
        $this->assertSame(
            [
                'animator_firstname' => 'Émmanuel',
                'event_name' => 'En Marche Lyon',
                'event_date' => 'mercredi 1 février 2017',
                'event_hour' => '15h30',
                'event_address' => '15 allées Paul Bocuse, 69006 Lyon 6e Arrondissement',
                'event_slug' => self::SHOW_EVENT_URL,
                'event-slug' => self::SHOW_EVENT_URL,
                'target_firstname' => 'Éric',
                'event_ok_link' => self::ACCEPT_EVENT_URL,
                'event_ko_link' => self::IGNORE_EVENT_URL,
            ],
            $recipient->getVars()
        );
    }

    private function createCommitteeEventMock(string $name, string $beginAt, string $street, string $cityCode): CommitteeEvent
    {
        $address = PostAddress::createFrenchAddress($street, $cityCode)->getInlineFormattedAddress('fr_FR');

        $event = $this->getMockBuilder(CommitteeEvent::class)->disableOriginalConstructor()->getMock();
        $event->expects($this->any())->method('getName')->willReturn($name);
        $event->expects($this->any())->method('getBeginAt')->willReturn(new \DateTime($beginAt));
        $event->expects($this->any())->method('getInlineFormattedAddress')->with('fr_FR')->willReturn($address);

        return $event;
    }

    private function createAdherentMock(string $emailAddress, string $firstName, string $lastName): Adherent
    {
        $adherent = $this->getMockBuilder(Adherent::class)->disableOriginalConstructor()->getMock();
        $adherent->expects($this->any())->method('getEmailAddress')->willReturn($emailAddress);
        $adherent->expects($this->any())->method('getFirstName')->willReturn($firstName);
        $adherent->expects($this->any())->method('getLastName')->willReturn($lastName);
        $adherent->expects($this->any())->method('getFullName')->willReturn($firstName.' '.$lastName);

        return $adherent;
    }
}
