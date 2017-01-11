<?php

namespace Tests\AppBundle\Mailjet;

use AppBundle\Mailjet\Event\MailjetEvent;
use AppBundle\Mailjet\Event\MailjetEvents;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\MailjetTemplateEmailFactory;
use AppBundle\Mailjet\Message\DummyMessage;
use AppBundle\Mailjet\Transport\MailjetFailingTransport;
use AppBundle\Mailjet\Transport\MailjetNullTransport;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MailjetServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testSendMessage()
    {
        $dispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();

        $dispatcher->expects($this->at(0))->method('dispatch')->with(
            $this->equalTo(MailjetEvents::DELIVERY_MESSAGE),
            $this->isInstanceOf(MailjetEvent::class)
        );

        $dispatcher->expects($this->at(1))->method('dispatch')->with(
            $this->equalTo(MailjetEvents::DELIVERY_SUCCESS),
            $this->isInstanceOf(MailjetEvent::class)
        );

        $service = new MailjetService($dispatcher, new MailjetNullTransport(), new MailjetTemplateEmailFactory('contact@en-marche.fr', 'En Marche'));

        $this->assertTrue($service->sendMessage(DummyMessage::create()));
    }

    public function testCannotSendMessage()
    {
        $dispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();

        $dispatcher->expects($this->at(0))->method('dispatch')->with(
            $this->equalTo(MailjetEvents::DELIVERY_MESSAGE),
            $this->isInstanceOf(MailjetEvent::class)
        );

        $dispatcher->expects($this->at(1))->method('dispatch')->with(
            $this->equalTo(MailjetEvents::DELIVERY_ERROR),
            $this->isInstanceOf(MailjetEvent::class)
        );

        $service = new MailjetService($dispatcher, new MailjetFailingTransport(), new MailjetTemplateEmailFactory('contact@en-marche.fr', 'En Marche'));

        $this->assertFalse($service->sendMessage(DummyMessage::create()));
    }
}
