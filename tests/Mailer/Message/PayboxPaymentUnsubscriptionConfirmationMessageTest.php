<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Mailer\Message\PayboxPaymentUnsubscriptionConfirmationMessage;

/**
 * @group message
 */
class PayboxPaymentUnsubscriptionConfirmationMessageTest extends MessageTestCase
{
    public function testCreate(): void
    {
        $message = PayboxPaymentUnsubscriptionConfirmationMessage::create(
            $this->createAdherent('david@example.com', 'David', 'Jones')
        );

        self::assertMessage(PayboxPaymentUnsubscriptionConfirmationMessage::class, [], $message);
        self::assertNoSender($message);
        self::assertNoReplyTo($message);
        self::assertNoCC($message);
        self::assertCountRecipients(1, $message);

        self::assertMessageRecipient(
            'david@example.com',
            'David Jones',
            ['recipient_first_name' => 'David'],
            $message
        );
    }
}
