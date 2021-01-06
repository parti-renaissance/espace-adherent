<?php

namespace Tests\App\Mailer;

use App\Mailer\EmailClientInterface;
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
            $this->isInstanceOf(MailerEvent::class),
            $this->equalTo(MailerEvents::DELIVERY_MESSAGE),
        );

        $dispatcher->expects($this->at(1))->method('dispatch')->with(
            $this->isInstanceOf(MailerEvent::class),
            $this->equalTo(MailerEvents::DELIVERY_SUCCESS)
        );

        $service = new MailerService(
            $dispatcher,
            new NullTransport(),
            new EmailTemplateFactory(
                'contact@en-marche.fr',
                'En Marche',
                DummyEmailTemplate::class
            ),
            $this->createMock(EmailClientInterface::class)
        );

        $this->assertTrue($service->sendMessage(DummyMessage::create()));
    }

    public function testCannotSendMessage()
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $dispatcher->expects($this->at(0))->method('dispatch')->with(
            $this->isInstanceOf(MailerEvent::class),
            $this->equalTo(MailerEvents::DELIVERY_MESSAGE)
        );

        $dispatcher->expects($this->at(1))->method('dispatch')->with(
            $this->isInstanceOf(MailerEvent::class),
            $this->equalTo(MailerEvents::DELIVERY_ERROR)
        );

        $service = new MailerService(
            $dispatcher,
            new FailingTransport(),
            new EmailTemplateFactory(
                'contact@en-marche.fr',
                'En Marche',
                DummyEmailTemplate::class
            ),
            $this->createMock(EmailClientInterface::class)
        );

        $this->assertFalse($service->sendMessage(DummyMessage::create()));
    }

    public function testRenderEmailTemplate(): void
    {
        $emailClientMock = $this->createConfiguredMock(EmailClientInterface::class, [
            'renderEmail' => '<p>email template</p>',
        ]);

        $service = new MailerService(
            $this->createMock(EventDispatcherInterface::class),
            new FailingTransport(),
            new EmailTemplateFactory(
                'contact@en-marche.fr',
                'En Marche',
                DummyEmailTemplate::class
            ),
            $emailClientMock
        );

        $this->assertSame('<p>email template</p>', $service->renderMessage(DummyMessage::create()));
    }
}
