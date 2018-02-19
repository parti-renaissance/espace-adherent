<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Mailer\Message\EventContactMembersMessage;

/**
 * @group message
 */
class EventContactMembersMessageTest extends MessageTestCase
{
    public function testCreate(): void
    {
        $message = EventContactMembersMessage::create(
            [
                $this->createEventRegistration('jean@example.com', 'Jean', 'Doe'),
                $this->createEventRegistration('bernard@example.com', 'Bernard', 'Smith'),
            ],
            $this->createAdherent('organizer@example.com', 'Organisateur', 'Jones'),
            'Sujet de test',
            'Contenu du message de test.'
        );

        self::assertMessage(
            EventContactMembersMessage::class,
            [
                'organizer_first_name' => 'Organisateur',
                'subject' => 'Sujet de test',
                'message' => 'Contenu du message de test.',
            ],
            $message
        );

        self::assertNoSender($message);
        self::assertReplyTo('organizer@example.com', $message);

        self::assertCountRecipients(2, $message);

        self::assertMessageRecipient(
            'jean@example.com',
            'Jean Doe',
            [
                'organizer_first_name' => 'Organisateur',
                'subject' => 'Sujet de test',
                'message' => 'Contenu du message de test.',
                'first_name' => 'Jean',
            ],
            $message
        );
        self::assertMessageRecipient(
            'bernard@example.com',
            'Bernard Smith',
            [
                'organizer_first_name' => 'Organisateur',
                'subject' => 'Sujet de test',
                'message' => 'Contenu du message de test.',
                'first_name' => 'Bernard',
            ],
            $message
        );

        self::assertNoCC($message);
    }
}
