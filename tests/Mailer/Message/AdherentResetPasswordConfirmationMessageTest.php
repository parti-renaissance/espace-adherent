<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Mailer\Message\AdherentResetPasswordConfirmationMessage;

/**
 * @group message
 */
class AdherentResetPasswordConfirmationMessageTest extends MessageTestCase
{
    public function testCreate(): void
    {
        $message = AdherentResetPasswordConfirmationMessage::create(
            $this->createAdherent('jean@example.com', 'Jean', 'Doe')
        );

        self::assertMessage(
            AdherentResetPasswordConfirmationMessage::class,
            [
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
                'first_name' => 'Jean',
            ],
            $message
        );

        self::assertNoCC($message);
    }
}
