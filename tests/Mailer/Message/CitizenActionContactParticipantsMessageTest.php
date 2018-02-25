<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Mailer\Message\CitizenActionContactParticipantsMessage;

/**
 * @group message
 */
class CitizenActionContactParticipantsMessageTest extends MessageTestCase
{
    public function testCreate(): void
    {
        $message = CitizenActionContactParticipantsMessage::create(
            [
                $this->createEventRegistration('jean@example.com', 'Jean', 'Doe'),
                $this->createEventRegistration('bernard@example.com', 'Bernard', 'Smith'),
            ],
            $this->createAdherent('organizer@example.com', 'Organisateur', 'Jones'),
            'Sujet de test',
            'Contenu du message de test.'
        );

        self::assertMessage(
            CitizenActionContactParticipantsMessage::class,
            [
                'citizen_project_host_first_name' => 'Organisateur',
                'citizen_project_host_subject' => 'Sujet de test',
                'citizen_project_host_message' => 'Contenu du message de test.',
            ],
            $message
        );

        self::assertSender('Organisateur Jones', null, $message);
        self::assertReplyTo('organizer@example.com', $message);

        self::assertCountRecipients(2, $message);

        self::assertMessageRecipient(
            'jean@example.com',
            'Jean Doe',
            [
                'citizen_project_host_first_name' => 'Organisateur',
                'citizen_project_host_subject' => 'Sujet de test',
                'citizen_project_host_message' => 'Contenu du message de test.',
            ],
            $message
        );
        self::assertMessageRecipient(
            'bernard@example.com',
            'Bernard Smith',
            [
                'citizen_project_host_first_name' => 'Organisateur',
                'citizen_project_host_subject' => 'Sujet de test',
                'citizen_project_host_message' => 'Contenu du message de test.',
            ],
            $message
        );

        self::assertNoCC($message);
    }
}
