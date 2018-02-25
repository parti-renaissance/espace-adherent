<?php

namespace Tests\AppBundle\Test\Mailer\Message;

use AppBundle\Mailer\Message\Message;
use Ramsey\Uuid\Uuid;

class DummyMessage extends Message
{
    public static function create()
    {
        return new self(
            Uuid::fromString('99999999-9999-9999-9999-999999999999'),
            'dummy@example.tld',
            'Dummy User',
            ['dummy' => 'ymmud']
        );
    }
}
