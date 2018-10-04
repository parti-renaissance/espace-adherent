<?php

namespace Tests\AppBundle\Mail\Transactional;

use AppBundle\Mail\Transactional\AdherentAccountActivationMail;
use Tests\AppBundle\Mail\MailTestCase;

/**
 * @group message
 */
class AdherentAccountActivationMailTest extends MailTestCase
{
    public function testCreate(): void
    {
        $recipient = AdherentAccountActivationMail::createRecipientFor(
            $this->createAdherent('jean@example.com'),
            'https://enmarche.code/activation/foo-bar'
        );

        self::assertMessageRecipient(
            'jean@example.com',
            'John Smith',
            [
                'first_name' => 'John',
                'activation_link' => 'https://enmarche.code/activation/foo-bar',
            ],
            $recipient
        );
    }
}
