<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Mailer\Message\CommitteeContactMembersMessage;

/**
 * @group message
 */
class CommitteeContactMembersMessageTest extends MessageTestCase
{
    public function testCreate(): void
    {
        $message = CommitteeContactMembersMessage::create(
            [
                $this->createAdherent('jean@example.com', 'Jean', 'Doe'),
                $this->createAdherent('bernard@example.com', 'Bernard', 'Smith'),
            ],
            $this->createAdherent('host@example.com', 'Animateur', 'Jones'),
            'Sujet de test',
            'Contenu du message de test.'
        );

        self::assertMessage(
            CommitteeContactMembersMessage::class,
            [
                'host_first_name' => 'Animateur',
                'subject' => 'Sujet de test',
                'message' => 'Contenu du message de test.',
            ],
            $message
        );

        self::assertSender('Animateur Jones', null, $message);
        self::assertReplyTo('host@example.com', $message);

        self::assertCountRecipients(2, $message);

        self::assertMessageRecipient(
            'jean@example.com',
            'Jean Doe',
            [
                'host_first_name' => 'Animateur',
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
                'host_first_name' => 'Animateur',
                'subject' => 'Sujet de test',
                'message' => 'Contenu du message de test.',
                'first_name' => 'Bernard',
            ],
            $message
        );

        self::assertNoCC($message);
    }
}
