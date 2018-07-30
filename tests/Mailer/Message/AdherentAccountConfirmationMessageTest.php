<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Mail\AdherentAccountConfirmationMail;

/**
 * @group message
 */
class AdherentAccountConfirmationMessageTest extends MessageTestCase
{
    public function testCreate(): void
    {
        $message = AdherentAccountConfirmationMail::create(
            $this->createAdherent('jean@example.com', 'Jean', 'Doe'),
            8,
            15
        );

        self::assertMessage(
            AdherentAccountConfirmationMail::class,
            [
                'adherents_count' => 8,
                'first_name' => 'Jean',
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
                'first_name' => 'Jean',
            ],
            $message
        );

        self::assertNoCC($message);
    }
}
