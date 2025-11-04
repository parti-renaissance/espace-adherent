<?php

namespace Tests\App\Mailer;

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
            new NullTransport(),
            new EmailTemplateFactory(
                'contact@en-marche.fr',
                'En Marche',
                $this->createMock(Manager::class)
            ),
            $dispatcher,
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
            new FailingTransport(),
            new EmailTemplateFactory(
                'contact@en-marche.fr',
                'En Marche',
                $this->createMock(Manager::class)
            ),
            $dispatcher,
        );

        $this->assertFalse($service->sendMessage(DummyMessage::create()));
    }
}
