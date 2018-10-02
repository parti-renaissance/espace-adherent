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
        self::assertMessageRecipient(
            'jean@example.com',
            'John Smith',
            ['target_firstname' => 'John'],
            AdherentAccountConfirmationMail::createRecipient($this->createAdherent('jean@example.com'))
        );
    }
}
