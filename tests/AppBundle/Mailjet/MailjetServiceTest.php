<?php

namespace Tests\AppBundle\Mailjet;

use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\DummyMessage;
use AppBundle\Mailjet\Transport\MailjetFailingTransport;
use AppBundle\Mailjet\Transport\MailjetNullTransport;

class MailjetServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testSendMessage()
    {
        $service = new MailjetService(new MailjetNullTransport(), 'contact@en-marche.fr', 'En Marche');

        $this->assertTrue($service->sendMessage(DummyMessage::create()));
    }

    public function testCannotSendMessage()
    {
        $service = new MailjetService(new MailjetFailingTransport(), 'contact@en-marche.fr', 'En Marche');

        $this->assertFalse($service->sendMessage(DummyMessage::create()));
    }
}
