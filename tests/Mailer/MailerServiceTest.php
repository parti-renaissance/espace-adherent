<?php

namespace Tests\App\Mailer;

use App\Mailer\EmailClientInterface;
use App\Mailer\EmailTemplateFactory;
use App\Mailer\Event\MailerEvent;
use App\Mailer\Event\MailerEvents;
use App\Mailer\MailerService;
use App\Mailer\Template\Manager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tests\App\Test\Mailer\Message\DummyMessage;
use Tests\App\Test\Mailer\Transport\FailingTransport;
use Tests\App\Test\Mailer\Transport\NullTransport;

class MailerServiceTest extends TestCase
{
    public function testSendMessage()
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $series = [
            [MailerEvents::BEFORE_EMAIL_BUILD],
            [MailerEvents::DELIVERY_MESSAGE],
            [MailerEvents::DELIVERY_SUCCESS],
        ];

        $dispatcher
            ->expects($this->exactly(3))
            ->method('dispatch')
            ->willReturnCallback(function (...$args) use (&$series) {
                $expectedArgs = array_shift($series);

                $this->assertInstanceOf(MailerEvent::class, $args[0]);
                $this->assertSame($expectedArgs[0], $args[1]);

                return $args[0];
            })
        ;

        $service = new MailerService(
            $dispatcher,
            new NullTransport(),
            new EmailTemplateFactory(
                'contact@en-marche.fr',
                'En Marche',
                $this->createMock(Manager::class)
            ),
            $this->createMock(EmailClientInterface::class)
        );

        $this->assertTrue($service->sendMessage(DummyMessage::create()));
    }

    public function testCannotSendMessage()
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $series = [
            [MailerEvents::BEFORE_EMAIL_BUILD],
            [MailerEvents::DELIVERY_MESSAGE],
            [MailerEvents::DELIVERY_ERROR],
        ];

        $dispatcher
            ->expects($this->exactly(3))
            ->method('dispatch')
            ->willReturnCallback(function (...$args) use (&$series) {
                $expectedArgs = array_shift($series);

                $this->assertInstanceOf(MailerEvent::class, $args[0]);
                $this->assertSame($expectedArgs[0], $args[1]);

                return $args[0];
            })
        ;

        $service = new MailerService(
            $dispatcher,
            new FailingTransport(),
            new EmailTemplateFactory(
                'contact@en-marche.fr',
                'En Marche',
                $this->createMock(Manager::class)
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
                $this->createMock(Manager::class)
            ),
            $emailClientMock
        );

        $this->assertSame('<p>email template</p>', $service->renderMessage(DummyMessage::create()));
    }
}
