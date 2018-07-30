<?php

namespace Tests\AppBundle\Mail\Transactional;

use AppBundle\Contact\ContactMessage;
use AppBundle\Mail\Campaign\AdherentContactMail;
use Tests\AppBundle\Mail\MailTestCase;

/**
 * @group message
 */
class AdherentContactMailTest extends MailTestCase
{
    public function testCreate(): void
    {
        $recipient = AdherentContactMail::createRecipientFor(
            new ContactMessage(
                $this->createAdherent('jean@example.com'),
                $this->createAdherent('vl@dimir.org'),
<<<MESSAGE
Bonsoir,
je vias bien,
voilà!
MESSAGE
            )
        );

        self::assertMessageRecipient(
            'vl@dimir.org',
            'John Smith',
            [
                'member_firstname' => 'John',
                'target_message' => <<<MESSAGE
Bonsoir,<br />
je vias bien,<br />
voilà!
MESSAGE
            ],
            $recipient
        );
    }
}
