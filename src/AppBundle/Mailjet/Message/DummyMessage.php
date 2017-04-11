<?php

namespace AppBundle\Mailjet\Message;

use Ramsey\Uuid\Uuid;

final class DummyMessage extends MailjetMessage
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
