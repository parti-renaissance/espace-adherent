<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Mailer\Message\EventNotificationMessage;
use AppBundle\Mailer\Message\MessageRecipient;

class EventNotificationMessageTest extends AbstractEventMessageTest
{
    const SHOW_EVENT_URL = 'https://test.enmarche.code/comites/59b1314d-dcfb-4a4c-83e1-212841d0bd0f/evenements/2017-01-31-en-marche-lyon';
    const ATTEND_EVENT_URL = 'https://test.enmarche.code/comites/59b1314d-dcfb-4a4c-83e1-212841d0bd0f/evenements/2017-01-31-en-marche-lyon/inscription';

    public function testCreateEventNotificationMessage()
    {
        $recipients[] = $this->createAdherentMock('em@example.com', 'Émmanuel', 'Macron');
        $recipients[] = $this->createAdherentMock('jb@example.com', 'Jean', 'Berenger');
        $recipients[] = $this->createAdherentMock('ml@example.com', 'Marie', 'Lambert');
        $recipients[] = $this->createAdherentMock('ez@example.com', 'Éric', 'Zitrone');

        $message = EventNotificationMessage::create(
            $recipients,
            $recipients[0],
            $this->createEventMock('En Marche Lyon', '2017-02-01 15:30:00', '15 allées Paul Bocuse', '69006-69386', 'EM Lyon'),
            self::SHOW_EVENT_URL,
            self::ATTEND_EVENT_URL,
            function (Adherent $adherent) {
                return EventNotificationMessage::getRecipientVars($adherent->getFirstName());
            }
        );

        $this->assertInstanceOf(EventNotificationMessage::class, $message);
        $this->assertSame('54917', $message->getTemplate());
        $this->assertCount(4, $message->getRecipients());
        $this->assertSame('1 février - 15h30 : Nouvel événement de EM Lyon : En Marche Lyon', $message->getSubject());
        $this->assertCount(10, $message->getVars());
        $this->assertSame(
            [
                'animator_firstname' => 'Émmanuel',
                'event_name' => 'En Marche Lyon',
                'event_date' => 'mercredi 1 février 2017',
                'event_hour' => '15h30',
                'event_address' => '15 allées Paul Bocuse, 69006 Lyon 6e',
                'event_slug' => self::SHOW_EVENT_URL,
                'event-slug' => self::SHOW_EVENT_URL,
                'event_ok_link' => self::ATTEND_EVENT_URL,
                'event_ko_link' => self::SHOW_EVENT_URL,
                'target_firstname' => '',
            ],
            $message->getVars()
        );

        $recipient = $message->getRecipient(0);
        $this->assertInstanceOf(MessageRecipient::class, $recipient);
        $this->assertSame('em@example.com', $recipient->getEmailAddress());
        $this->assertSame('Émmanuel Macron', $recipient->getFullName());
        $this->assertSame(
            [
                'animator_firstname' => 'Émmanuel',
                'event_name' => 'En Marche Lyon',
                'event_date' => 'mercredi 1 février 2017',
                'event_hour' => '15h30',
                'event_address' => '15 allées Paul Bocuse, 69006 Lyon 6e',
                'event_slug' => self::SHOW_EVENT_URL,
                'event-slug' => self::SHOW_EVENT_URL,
                'event_ok_link' => self::ATTEND_EVENT_URL,
                'event_ko_link' => self::SHOW_EVENT_URL,
                'target_firstname' => 'Émmanuel',
            ],
            $recipient->getVars()
        );

        $recipient = $message->getRecipient(3);
        $this->assertInstanceOf(MessageRecipient::class, $recipient);
        $this->assertSame('ez@example.com', $recipient->getEmailAddress());
        $this->assertSame('Éric Zitrone', $recipient->getFullName());
        $this->assertSame(
            [
                'animator_firstname' => 'Émmanuel',
                'event_name' => 'En Marche Lyon',
                'event_date' => 'mercredi 1 février 2017',
                'event_hour' => '15h30',
                'event_address' => '15 allées Paul Bocuse, 69006 Lyon 6e',
                'event_slug' => self::SHOW_EVENT_URL,
                'event-slug' => self::SHOW_EVENT_URL,
                'event_ok_link' => self::ATTEND_EVENT_URL,
                'event_ko_link' => self::SHOW_EVENT_URL,
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
            self::ATTEND_EVENT_URL,
            function (Adherent $adherent) {
                return EventNotificationMessage::getRecipientVars($adherent->getFirstName());
            }
        );

        $this->assertInstanceOf(EventNotificationMessage::class, $message);
        $this->assertSame('54917', $message->getTemplate());
        $this->assertCount(1, $message->getRecipients());
        $this->assertSame('14 février - 08h00 : Nouvel événement de EM Lyon : petit-dejeuner', $message->getSubject());
        $this->assertCount(10, $message->getVars());
        $this->assertSame(
            [
                'animator_firstname' => 'Émmanuel',
                'event_name' => 'petit-dejeuner',
                'event_date' => 'jeudi 14 février 2019',
                'event_hour' => '08h00',
                'event_address' => 'conrad hong-kong pacific place 88, 69006 Lyon 6e',
                'event_slug' => self::SHOW_EVENT_URL,
                'event-slug' => self::SHOW_EVENT_URL,
                'event_ok_link' => self::ATTEND_EVENT_URL,
                'event_ko_link' => self::SHOW_EVENT_URL,
                'target_firstname' => '',
            ],
            $message->getVars()
        );
    }
}
