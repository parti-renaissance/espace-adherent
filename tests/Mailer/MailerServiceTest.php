<?php

namespace Tests\AppBundle\Mailer;

use AppBundle\Mailer\Event\MailerEvent;
use AppBundle\Mailer\Event\MailerEvents;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\EmailTemplateFactory;
use AppBundle\Mailer\Message\MessageRegistry;
use PHPUnit\Framework\TestCase;
use Tests\AppBundle\Test\Mailer\DummyEmailTemplate;
use Tests\AppBundle\Test\Mailer\Message\DummyMessage;
use Tests\AppBundle\Test\Mailer\Transport\FailingTransport;
use Tests\AppBundle\Test\Mailer\Transport\NullTransport;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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

        $messageRegistry = $this->createMock(MessageRegistry::class);
        $messageRegistry->expects($this->once())->method('getTemplate')->willReturn('dummy_message');

        $service = new MailerService(
            $dispatcher,
            new NullTransport(),
            new EmailTemplateFactory(
                $messageRegistry,
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

        $messageRegistry = $this->createMock(MessageRegistry::class);
        $messageRegistry->expects($this->once())->method('getTemplate')->willReturn('dummy_message');

        $service = new MailerService(
            $dispatcher,
            new FailingTransport(),
            new EmailTemplateFactory(
                $messageRegistry,
                'contact@en-marche.fr',
                'En Marche',
                DummyEmailTemplate::class
            )
        );

        $this->assertFalse($service->sendMessage(DummyMessage::create()));
    }
}
