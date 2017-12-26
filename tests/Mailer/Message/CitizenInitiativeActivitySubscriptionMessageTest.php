<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Mailer\Message\CitizenInitiativeActivitySubscriptionMessage;
use AppBundle\Mailer\Message\MessageRecipient;

class CitizenInitiativeActivitySubscriptionMessageTest extends AbstractEventMessageTest
{
    const SHOW_CITIZEN_INITIATIVE_URL = 'https://enmarche.dev/comites/59b1314d-dcfb-4a4c-83e1-212841d0bd0f/evenements/2017-01-31-en-marche-lyon';

    public function testCreateCitizenInitiativeActivitySubscriptionMessage()
    {
        $recipients[] = $this->createAdherentMock('em@example.com', 'Émmanuel', 'Macron');
        $recipients[] = $this->createAdherentMock('jb@example.com', 'Jean', 'Berenger');
        $recipients[] = $this->createAdherentMock('ml@example.com', 'Marie', 'Lambert');
        $recipients[] = $this->createAdherentMock('ez@example.com', 'Éric', 'Zitrone');

        $message = CitizenInitiativeActivitySubscriptionMessage::create(
            $recipients,
            $recipients[0],
            $this->createCitizenInitiativeMock('Initiative citoyenne à Lyon', '2017-11-15 10:30:00', '15 allées Paul Bocuse', '69006-69386', self::SHOW_CITIZEN_INITIATIVE_URL),
            self::SHOW_CITIZEN_INITIATIVE_URL,
            function (Adherent $adherent) {
                return CitizenInitiativeActivitySubscriptionMessage::getRecipientVars($adherent->getFirstName());
            }
        );

        $this->assertInstanceOf(CitizenInitiativeActivitySubscriptionMessage::class, $message);
        $this->assertSame('196524', $message->getTemplate());
        $this->assertCount(4, $message->getRecipients());
        $this->assertSame('Nouvelle initiative citoyenne : Initiative citoyenne à Lyon', $message->getSubject());
        $this->assertCount(7, $message->getVars());
        $this->assertSame(
            [
                'IC_organizer_firstname' => 'Émmanuel',
                'IC_organizer_lastname' => 'Macron',
                'IC_name' => 'Initiative citoyenne à Lyon',
                'IC_date' => 'mercredi 15 novembre 2017',
                'IC_hour' => '10h30',
                'IC_address' => '15 allées Paul Bocuse, 69006 Lyon 6e',
                'IC_link' => self::SHOW_CITIZEN_INITIATIVE_URL,
            ],
            $message->getVars()
        );

        $recipient = $message->getRecipient(0);
        $this->assertInstanceOf(MessageRecipient::class, $recipient);
        $this->assertSame('em@example.com', $recipient->getEmailAddress());
        $this->assertSame('Émmanuel Macron', $recipient->getFullName());
        $this->assertSame(
            [
                'IC_organizer_firstname' => 'Émmanuel',
                'IC_organizer_lastname' => 'Macron',
                'IC_name' => 'Initiative citoyenne à Lyon',
                'IC_date' => 'mercredi 15 novembre 2017',
                'IC_hour' => '10h30',
                'IC_address' => '15 allées Paul Bocuse, 69006 Lyon 6e',
                'IC_link' => self::SHOW_CITIZEN_INITIATIVE_URL,
                'prenom' => 'Émmanuel',
            ],
            $recipient->getVars()
        );

        $recipient = $message->getRecipient(3);
        $this->assertInstanceOf(MessageRecipient::class, $recipient);
        $this->assertSame('ez@example.com', $recipient->getEmailAddress());
        $this->assertSame('Éric Zitrone', $recipient->getFullName());
        $this->assertSame(
            [
                'IC_organizer_firstname' => 'Émmanuel',
                'IC_organizer_lastname' => 'Macron',
                'IC_name' => 'Initiative citoyenne à Lyon',
                'IC_date' => 'mercredi 15 novembre 2017',
                'IC_hour' => '10h30',
                'IC_address' => '15 allées Paul Bocuse, 69006 Lyon 6e',
                'IC_link' => self::SHOW_CITIZEN_INITIATIVE_URL,
                'prenom' => 'Éric',
            ],
            $recipient->getVars()
        );
    }
}
