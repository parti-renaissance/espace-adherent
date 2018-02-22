<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Mailer\Message\AdherentAccountConfirmationMessage;

/**
 * @group message
 */
class AdherentAccountConfirmationMessageTest extends MessageTestCase
{
    public function testCreate(): void
    {
        $message = AdherentAccountConfirmationMessage::create(
            $this->createAdherent('jean@example.com', 'Jean', 'Doe'),
            8,
            15
        );

        self::assertMessage(
            AdherentAccountConfirmationMessage::class,
            [
                'adherents_count' => 8,
                'committees_count' => 15,
                'first_name' => 'Jean',
                'last_name' => 'Doe',
            ],
            $message
        );

        self::assertNoSender($message);
        self::assertNoReplyTo($message);

        self::assertCountRecipients(1, $message);

        self::assertMessageRecipient(
            'jean@example.com',
            'Jean Doe',
            [
                'adherents_count' => 8,
                'committees_count' => 15,
                'first_name' => 'Jean',
                'last_name' => 'Doe',
            ],
            $message
        );

        self::assertNoCC($message);
    }
}
