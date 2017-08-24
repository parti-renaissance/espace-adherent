<?php

namespace Tests\AppBundle\Mailjet\Message;

use AppBundle\Mailjet\Message\CitizenInitiativeOrganizerValidationMessage;

class CitizenInitiativeOrganizerValidationMessageTest extends AbstractEventMessageTest
{
    const SHOW_CITIZEN_INITIATIVE_URL = 'https://localhost/comites/59b1314d-dcfb-4a4c-83e1-212841d0bd0f/evenements/2017-01-31-en-marche-lyon';

    public function testCreateCitizenInitiativeAdherentsNearMessage()
    {
        $adherent[] = $this->createAdherentMock('em@example.com', 'Émmanuel', 'Macron');
        $recipients[] = $this->createAdherentMock('jb@example.com', 'Jean', 'Berenger');
        $recipients[] = $this->createAdherentMock('ml@example.com', 'Marie', 'Lambert');
        $recipients[] = $this->createAdherentMock('ez@example.com', 'Éric', 'Zitrone');

        $message = CitizenInitiativeOrganizerValidationMessage::create(
            $this->createAdherentMock('em@example.com', 'Émmanuel', 'Macron'),
            $this->createCitizenInitiativeMock('En Marche Lyon', '2017-02-01 15:30:00', '15 allées Paul Bocuse', '69006-69386', 'en-marche-lyon'),
            self::SHOW_CITIZEN_INITIATIVE_URL
        );

        $this->assertInstanceOf(CitizenInitiativeOrganizerValidationMessage::class, $message);
        $this->assertSame('196469', $message->getTemplate());
        $this->assertSame('Validation de votre initiative citoyenne', $message->getSubject());
        $this->assertCount(7, $message->getVars());
        $this->assertSame(
            [
                'prenom' => 'Émmanuel',
                'IC_name' => 'En Marche Lyon',
                'IC_date' => 'mercredi 1 février 2017',
                'IC_hour' => '15h30',
                'IC_address' => '15 allées Paul Bocuse, 69006 Lyon 6e',
                'IC_slug' => 'en-marche-lyon',
                'IC_link' => self::SHOW_CITIZEN_INITIATIVE_URL,
            ],
            $message->getVars()
        );
    }
}
