<?php

namespace Tests\AppBundle\Mail\Transactional;

use AppBundle\Mail\Transactional\AdherentAccountConfirmationMail;
use Tests\AppBundle\Mail\MailTestCase;

/**
 * @group message
 */
class AdherentAccountConfirmationMailTest extends MailTestCase
{
    public function testCreate(): void
    {
        $recipient = AdherentAccountConfirmationMail::createRecipientFor(
            $this->createAdherent('jean@example.com')
        );

        self::assertMessageRecipient(
            'jean@example.com',
            'John Smith',
            ['target_firstname' => 'John'],
            $recipient
        );
    }
}
