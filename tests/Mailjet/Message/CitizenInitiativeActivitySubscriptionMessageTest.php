<?php

namespace Tests\AppBundle\Mailjet\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Mailjet\Message\CitizenInitiativeActivitySubscriptionMessage;
use AppBundle\Mailjet\Message\MailjetMessageRecipient;

class CitizenInitiativeActivitySubscriptionMessageTest extends AbstractEventMessageTest
{
    const SHOW_CITIZEN_INITIATIVE_URL = 'https://localhost/initiative_citoyenne/5ddd0c90-e804-4396-bcc1-a51285cc9061/2017-11-15-initiative-citoyenne-a-lyon';
    const CITIZEN_INITIATIVE_SLUG = '2017-11-15-initiative-citoyenne-a-lyon';

    public function testCreateCitizenInitiativeActivitySubscriptionMessage()
    {
        $recipients[] = $this->createAdherentMock('em@example.com', 'Émmanuel', 'Macron');
        $recipients[] = $this->createAdherentMock('jb@example.com', 'Jean', 'Berenger');
        $recipients[] = $this->createAdherentMock('ml@example.com', 'Marie', 'Lambert');
        $recipients[] = $this->createAdherentMock('ez@example.com', 'Éric', 'Zitrone');

        $message = CitizenInitiativeActivitySubscriptionMessage::create(
            $recipients,
            $recipients[0],
            $this->createCitizenInitiativeMock('Initiative citoyenne à Lyon', '2017-11-15 10:30:00', '15 allées Paul Bocuse', '69006-69386', self::CITIZEN_INITIATIVE_SLUG),
            self::SHOW_CITIZEN_INITIATIVE_URL,
            function (Adherent $adherent) {
                return CitizenInitiativeActivitySubscriptionMessage::getRecipientVars($adherent->getFirstName());
            }
        );

        $this->assertInstanceOf(CitizenInitiativeActivitySubscriptionMessage::class, $message);
        $this->assertSame('196480', $message->getTemplate());
        $this->assertCount(4, $message->getRecipients());
        $this->assertSame('15 novembre - 10h30 : Nouvelle initiative citoyenne : Initiative citoyenne à Lyon', $message->getSubject());
        $this->assertCount(9, $message->getVars());
        $this->assertSame(
            [
                'IC_organizer_firstname' => 'Émmanuel',
                'IC_organizer_lastname' => 'Macron',
                'IC_name' => 'Initiative citoyenne à Lyon',
                'IC_date' => 'mercredi 15 novembre 2017',
                'IC_hour' => '10h30',
                'IC_address' => '15 allées Paul Bocuse, 69006 Lyon 6e',
                'IC_slug' => self::CITIZEN_INITIATIVE_SLUG,
                'IC_link' => self::SHOW_CITIZEN_INITIATIVE_URL,
                'target_firstname' => '',
            ],
            $message->getVars()
        );

        $recipient = $message->getRecipient(0);
        $this->assertInstanceOf(MailjetMessageRecipient::class, $recipient);
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
                'IC_slug' => self::CITIZEN_INITIATIVE_SLUG,
                'IC_link' => self::SHOW_CITIZEN_INITIATIVE_URL,
                'target_firstname' => 'Émmanuel',
            ],
            $recipient->getVars()
        );

        $recipient = $message->getRecipient(3);
        $this->assertInstanceOf(MailjetMessageRecipient::class, $recipient);
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
                'IC_slug' => self::CITIZEN_INITIATIVE_SLUG,
                'IC_link' => self::SHOW_CITIZEN_INITIATIVE_URL,
                'target_firstname' => 'Éric',
            ],
            $recipient->getVars()
        );
    }
}
