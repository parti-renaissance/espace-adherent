<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Mailer\Message\AdherentResetPasswordMessage;

/**
 * @group message
 */
class AdherentResetPasswordMessageTest extends MessageTestCase
{
    public function testCreateFromAdherent(): void
    {
        $message = AdherentResetPasswordMessage::createFromAdherent(
            $this->createAdherent('jean@example.com', 'Jean', 'Doe'),
            'https://enmarche.code/reset-password/foo-bar'
        );

        self::assertMessage(
            AdherentResetPasswordMessage::class,
            [
                'first_name' => 'Jean',
                'reset_link' => 'https://enmarche.code/reset-password/foo-bar',
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
                'reset_link' => 'https://enmarche.code/reset-password/foo-bar',
            ],
            $message
        );

        self::assertNoCC($message);
    }
}
