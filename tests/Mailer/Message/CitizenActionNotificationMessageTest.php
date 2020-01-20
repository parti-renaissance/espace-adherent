<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Mailer\Message\CitizenActionNotificationMessage;

class CitizenActionNotificationMessageTest extends AbstractEventMessageTest
{
    const ATTEND_EVENT_URL = 'https://test.enmarche.code/comites/59b1314d-dcfb-4a4c-83e1-212841d0bd0f/evenements/2017-01-31-en-marche-lyon/inscription';

    public function testCitizenActionNotificationMessage(): void
    {
        $recipients[] = $this->createAdherentMock('em@example.com', 'Émmanuel', 'Macron');

        $citizenAction = $this->createCitizenActionMock(
            'En Marche Lyon',
            '2017-02-01 15:30:00',
            '15 allées Paul Bocuse',
            '69006-69386',
            'Europe/Paris'
        );

        $message = CitizenActionNotificationMessage::create(
            $recipients,
            $recipients[0],
            $citizenAction,
            self::ATTEND_EVENT_URL
        );

        $this->assertCount(1, $message->getRecipients());
        $this->assertSame('[Projets citoyens] Une nouvelle action citoyenne au sein de votre projet citoyen !', $message->getSubject());
        $this->assertCount(7, $message->getVars());
        $this->assertSame(
            [
                'host_first_name' => 'Émmanuel',
                'citizen_project_name' => '',
                'citizen_action_name' => 'En Marche Lyon',
                'citizen_action_date' => 'mercredi 1 février 2017',
                'citizen_action_hour' => '15h30',
                'citizen_action_address' => '15 allées Paul Bocuse, 69006 Lyon 6e',
                'citizen_action_attend_link' => self::ATTEND_EVENT_URL,
            ],
            $message->getVars()
        );
    }

    public function testCitizenActionNotificationMessageTimeZone(): void
    {
        $recipients[] = $this->createAdherentMock('em@example.com', 'Émmanuel', 'Macron');

        $citizenAction = $this->createCitizenActionMock(
            'petit-dejeuner',
            '2019-02-14 01:00:00',
            'conrad hong-kong pacific place 88',
            '69006-69386',
            'Asia/Hong_Kong'
        );

        $message = CitizenActionNotificationMessage::create(
            $recipients,
            $recipients[0],
            $citizenAction,
            self::ATTEND_EVENT_URL
        );

        $this->assertCount(1, $message->getRecipients());
        $this->assertSame('[Projets citoyens] Une nouvelle action citoyenne au sein de votre projet citoyen !', $message->getSubject());
        $this->assertCount(7, $message->getVars());
        $this->assertSame(
            [
                'host_first_name' => 'Émmanuel',
                'citizen_project_name' => '',
                'citizen_action_name' => 'petit-dejeuner',
                'citizen_action_date' => 'jeudi 14 février 2019',
                'citizen_action_hour' => '08h00',
                'citizen_action_address' => 'conrad hong-kong pacific place 88, 69006 Lyon 6e',
                'citizen_action_attend_link' => self::ATTEND_EVENT_URL,
            ],
            $message->getVars()
        );
    }
}
