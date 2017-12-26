<?php

namespace AppBundle\Mailer\Message;

use Tests\AppBundle\Mailer\Message\AbstractEventMessageTest;

class CommitteeCitizenInitiativeOrganizerNotificationMessageTest extends AbstractEventMessageTest
{
    const CONTACT_ADHERENT_URL = 'https://enmarche.dev/espace-adherent/contacter/a9fc8d48-6f57-4d89-ae73-50b3f9b586f4?from=committee&id=464d4c23-cf4c-4d3a-8674-a43910da6419';

    public function testCreateCreateCommitteeCitizenInitiativeOrganizerNotificationMessage()
    {
        $message = CommitteeCitizenInitiativeOrganizerNotificationMessage::create(
            $this->createAdherentMock('em@example.com', 'Émmanuel', 'Macron'),
            $this->createCommitteeFeedItemMock(
                $this->createAdherentMock('kl@exemple.com', 'Kévin', 'Lafont'),
                'Cette initiative est superbe !',
                $this->createCitizenInitiativeMock(
                    'Apprenez à sauver des vies',
                    '2017-02-01 15:30:00',
                    '15 allées Paul Bocuse',
                    '69006-69386',
                    'apprenez-a-sauver-des-vies',
                    $this->createAdherentMock('jb@exemple.com', 'Jean', 'Baptise')
                ),
                'Comité En Marche'
            ),
            self::CONTACT_ADHERENT_URL
        );

        $this->assertInstanceOf(CommitteeCitizenInitiativeOrganizerNotificationMessage::class, $message);
        $this->assertSame('196522', $message->getTemplate());
        $this->assertSame('Votre initiative citoyenne a été partagée', $message->getSubject());
        $this->assertCount(5, $message->getVars());
        $this->assertSame(
            [
                'animator_firstname' => 'Kévin',
                'animator_lastname' => 'Lafont',
                'animator_contact_link' => self::CONTACT_ADHERENT_URL,
                'committee_name' => 'Comité En Marche',
                'IC_name' => 'Apprenez à sauver des vies',
            ],
            $message->getVars()
        );
    }
}
