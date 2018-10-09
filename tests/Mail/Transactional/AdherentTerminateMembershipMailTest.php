<?php

namespace Tests\AppBundle\Mail\Transactional;

use AppBundle\Mail\Transactional\AdherentTerminateMembershipMail;
use Tests\AppBundle\Mail\MailTestCase;

/**
 * @group message
 */
class AdherentTerminateMembershipMailTest extends MailTestCase
{
    public function testCreateFromAdherent(): void
    {
        self::assertMessageRecipient(
            'jean@example.com',
            'John Smith',
            ['target_firstname' => 'John'],
            AdherentTerminateMembershipMail::createRecipient($this->createAdherent('jean@example.com'))
        );
    }
}
