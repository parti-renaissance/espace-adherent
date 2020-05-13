<?php

namespace Tests\App\Mailer;

use App\Mailer\EmailTemplateFactory;
use App\Mailer\Event\MailerEvent;
use App\Mailer\Event\MailerEvents;
use App\Mailer\MailerService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tests\App\Test\Mailer\DummyEmailTemplate;
use Tests\App\Test\Mailer\Message\DummyMessage;
use Tests\App\Test\Mailer\Transport\FailingTransport;
use Tests\App\Test\Mailer\Transport\NullTransport;

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
