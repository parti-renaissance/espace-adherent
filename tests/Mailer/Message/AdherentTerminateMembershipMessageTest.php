<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Mail\AdherentTerminateMembershipMail;

/**
 * @group message
 */
class AdherentTerminateMembershipMessageTest extends MessageTestCase
{
    public function testCreateFromAdherent(): void
    {
        $message = \AppBundle\Mail\AdherentTerminateMembershipMail::create(
            $this->createAdherent('jean@example.com', 'Jean', 'Doe')
        );

        self::assertMessage(
            \AppBundle\Mail\AdherentTerminateMembershipMail::class,
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

    protected function tearDown()
    {
        $this->cleanupContainer($this->container);

        $this->container = null;
        $this->adherents = null;

        parent::tearDown();
    }
}
