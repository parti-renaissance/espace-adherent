<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Mailer\Message\AdherentAccountActivationMessage;

/**
 * @group message
 */
class AdherentAccountActivationMessageTest extends MessageTestCase
{
    public function testCreate(): void
    {
        $message = AdherentAccountActivationMessage::create(
            $this->createAdherent('jean@example.com', 'Jean', 'Doe'),
            'https://enmarche.code/activation/foo-bar'
        );

        self::assertMessage(
            AdherentAccountActivationMessage::class,
            [
                'first_name' => 'Jean',
                'activation_url' => 'https://enmarche.code/activation/foo-bar',
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
                'activation_url' => 'https://enmarche.code/activation/foo-bar',
            ],
            $message
        );

        self::assertNoCC($message);
    }
}
