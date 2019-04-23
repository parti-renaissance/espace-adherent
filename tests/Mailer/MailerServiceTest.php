<?php

namespace Tests\AppBundle\Mailer;

use AppBundle\Mailer\EmailTemplateFactory;
use AppBundle\Mailer\Event\MailerEvent;
use AppBundle\Mailer\Event\MailerEvents;
use AppBundle\Mailer\MailerService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tests\AppBundle\Test\Mailer\DummyEmailTemplate;
use Tests\AppBundle\Test\Mailer\Message\DummyMessage;
use Tests\AppBundle\Test\Mailer\Transport\FailingTransport;
use Tests\AppBundle\Test\Mailer\Transport\NullTransport;

class MailerServiceTest extends TestCase
{
    public function testSendMessage()
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $dispatcher->expects($this->at(0))->method('dispatch')->with(
            $this->equalTo(MailerEvents::DELIVERY_MESSAGE),
            $this->isInstanceOf(MailerEvent::class)
        );

        $dispatcher->expects($this->at(1))->method('dispatch')->with(
            $this->equalTo(MailerEvents::DELIVERY_SUCCESS),
            $this->isInstanceOf(MailerEvent::class)
        );

        $service = new MailerService(
            $dispatcher,
            new NullTransport(),
            new EmailTemplateFactory(
                'contact@en-marche.fr',
                'En Marche',
                DummyEmailTemplate::class
            )
        );

        $this->assertTrue($service->sendMessage(DummyMessage::create()));
    }

    public function testCannotSendMessage()
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $dispatcher->expects($this->at(0))->method('dispatch')->with(
            $this->equalTo(MailerEvents::DELIVERY_MESSAGE),
            $this->isInstanceOf(MailerEvent::class)
        );

        $dispatcher->expects($this->at(1))->method('dispatch')->with(
            $this->equalTo(MailerEvents::DELIVERY_ERROR),
            $this->isInstanceOf(MailerEvent::class)
        );

        $service = new MailerService(
            $dispatcher,
            new FailingTransport(),
            new EmailTemplateFactory(
                'contact@en-marche.fr',
                'En Marche',
                DummyEmailTemplate::class
            )
        );

        $this->assertFalse($service->sendMessage(DummyMessage::create()));
    }
}
