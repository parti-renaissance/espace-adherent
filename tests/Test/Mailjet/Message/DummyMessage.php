<?php

namespace Tests\AppBundle\Test\Mailjet\Message;

use AppBundle\Mailjet\Message\MailjetMessage;
use Ramsey\Uuid\Uuid;

class DummyMessage extends MailjetMessage
{
    public static function create()
    {
        return new self(
            Uuid::fromString('99999999-9999-9999-9999-999999999999'),
            '66666',
            'dummy@example.tld',
            'Dummy User',
            'Dummy Message',
            ['dummy' => 'ymmud']
        );
    }
}
