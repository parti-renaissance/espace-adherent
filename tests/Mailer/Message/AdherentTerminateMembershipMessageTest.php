<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Mailer\Message\AdherentTerminateMembershipMessage;

/**
 * @group message
 */
class AdherentTerminateMembershipMessageTest extends MessageTestCase
{
    public function testCreateFromAdherent(): void
    {
        $message = AdherentTerminateMembershipMessage::create(
            $this->createAdherent('jean@example.com', 'Jean', 'Doe')
        );

        self::assertMessage(
            AdherentTerminateMembershipMessage::class,
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
