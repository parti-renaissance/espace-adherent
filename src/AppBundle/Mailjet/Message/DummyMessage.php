<?php

namespace AppBundle\Mailjet\Message;

final class DummyMessage extends MailjetMessage
{
    public static function create()
    {
        return new static(
            '66666',
            'dummy@example.tld',
            'Dummy User',
            'Dummy Message',
            ['dummy' => 'ymmud']
        );
    }
}
