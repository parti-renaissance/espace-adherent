<?php

namespace Tests\AppBundle\Mailjet;

use AppBundle\Mailjet\Event\MailjetEvent;
use AppBundle\Mailjet\Event\MailjetEvents;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\EmailTemplateFactory;
use Tests\AppBundle\Test\Mailjet\Message\DummyMessage;
use Tests\AppBundle\Test\Mailjet\Transport\FailingTransport;
use Tests\AppBundle\Test\Mailjet\Transport\NullTransport;
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

        $service = new MailjetService($dispatcher, new NullTransport(), new EmailTemplateFactory('contact@en-marche.fr', 'En Marche'));

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

        $service = new MailjetService($dispatcher, new FailingTransport(), new EmailTemplateFactory('contact@en-marche.fr', 'En Marche'));

        $this->assertFalse($service->sendMessage(DummyMessage::create()));
    }
}
