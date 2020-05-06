<?php

namespace Tests\App\Mailer\Message;

use App\Entity\Adherent;
use App\Mailer\Message\EventNotificationMessage;
use App\Mailer\Message\MessageRecipient;

class EventNotificationMessageTest extends AbstractEventMessageTest
{
    private const SHOW_EVENT_URL = 'https://test.enmarche.code/comites/59b1314d-dcfb-4a4c-83e1-212841d0bd0f/evenements/2017-01-31-en-marche-lyon';

    public function testCreateEventNotificationMessage()
    {
        $recipients[] = $this->createAdherentMock('em@example.com', 'Émmanuel', 'Macron');
        $recipients[] = $this->createAdherentMock('jb@example.com', 'Jean', 'Berenger');
        $recipients[] = $this->createAdherentMock('ml@example.com', 'Marie', 'Lambert');
        $recipients[] = $this->createAdherentMock('ez@example.com', 'Éric', 'Zitrone');

        $message = EventNotificationMessage::create(
            $recipients,
            $recipients[0],
            $this->createEventMock(
                'En Marche Lyon',
                '2017-02-01 15:30:00',
                '15 allées Paul Bocuse',
                '69006-69386',
                'EM Lyon',
                'Europe/Paris',
                'Donec non dolor a sapien luctus lacinia id auctor orci'
            ),
            self::SHOW_EVENT_URL,
            function (Adherent $adherent) {
                return EventNotificationMessage::getRecipientVars($adherent->getFirstName());
            }
        );

        $this->assertSame('event-notification', $message->generateTemplateName());
        $this->assertCount(4, $message->getRecipients());
        $this->assertSame('1 février - 15h30 : Nouvel événement de EM Lyon : En Marche Lyon', $message->getSubject());
        $this->assertCount(8, $message->getVars());
        $this->assertSame(
            [
                'animator_firstname' => 'Émmanuel',
                'event_name' => 'En Marche Lyon',
                'event_date' => 'mercredi 1 février 2017',
                'event_hour' => '15h30',
                'event_address' => '15 allées Paul Bocuse, 69006 Lyon 6e',
                'event_slug' => self::SHOW_EVENT_URL,
                'event_description' => 'Donec non dolor a sapien luctus lacinia id auctor orci',
                'committee_name' => 'EM Lyon',
            ],
            $message->getVars()
        );

        $recipient = $message->getRecipient(0);
        $this->assertInstanceOf(MessageRecipient::class, $recipient);
        $this->assertSame('em@example.com', $recipient->getEmailAddress());
        $this->assertSame('Émmanuel Macron', $recipient->getFullName());
        $this->assertSame(['target_firstname' => 'Émmanuel'], $recipient->getVars());

        $recipient = $message->getRecipient(3);
        $this->assertInstanceOf(MessageRecipient::class, $recipient);
        $this->assertSame('ez@example.com', $recipient->getEmailAddress());
        $this->assertSame('Éric Zitrone', $recipient->getFullName());
        $this->assertSame(
            [
                'target_firstname' => 'Éric',
            ],
            $recipient->getVars()
        );
    }

    public function testCreateEventNotificationMessageTimeZone(): void
    {
        $recipients[] = $this->createAdherentMock('em@example.com', 'Émmanuel', 'Macron');
        $message = EventNotificationMessage::create(
            $recipients,
            $recipients[0],
            $this->createEventMock('petit-dejeuner', '2019-02-14 01:00:00', 'conrad hong-kong pacific place 88', '69006-69386', 'EM Lyon', 'Asia/Hong_Kong'),
            self::SHOW_EVENT_URL,
            function (Adherent $adherent) {
                return EventNotificationMessage::getRecipientVars($adherent->getFirstName());
            }
        );

        $this->assertCount(1, $message->getRecipients());
        $this->assertSame('14 février - 08h00 : Nouvel événement de EM Lyon : petit-dejeuner', $message->getSubject());
        $this->assertCount(8, $message->getVars());
        $this->assertSame(
            [
                'animator_firstname' => 'Émmanuel',
                'event_name' => 'petit-dejeuner',
                'event_date' => 'jeudi 14 février 2019',
                'event_hour' => '08h00',
                'event_address' => 'conrad hong-kong pacific place 88, 69006 Lyon 6e',
                'event_slug' => self::SHOW_EVENT_URL,
                'event_description' => '',
                'committee_name' => 'EM Lyon',
            ],
            $message->getVars()
        );
    }
}
