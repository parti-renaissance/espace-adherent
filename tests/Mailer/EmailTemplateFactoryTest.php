<?php

declare(strict_types=1);

namespace Tests\App\Mailer;

use App\Entity\Email\EmailSender;
use App\Entity\Email\TransactionalEmailTemplate;
use App\Mailer\EmailTemplateFactory;
use App\Mailer\Message\Message;
use App\Mailer\Template\Manager;
use PHPUnit\Framework\TestCase;
use Tests\App\Test\Mailer\Message\DummyMessage;

class EmailTemplateFactoryTest extends TestCase
{
    public function testTemplateSenderTakesPrecedence(): void
    {
        $message = DummyMessage::create();
        $template = new TransactionalEmailTemplate();
        $template->sender = $this->createSender('Foo', 'foo@example.org');

        $factory = new EmailTemplateFactory('system@example.org', 'System', $this->managerReturning($message, $template));

        $body = $factory->createFromMessage($message)->getBody();

        self::assertSame('foo@example.org', $body['message']['from_email']);
        self::assertSame('Foo', $body['message']['from_name']);
    }

    public function testFallsBackToMessageSenderWhenTemplateHasNoSender(): void
    {
        $message = DummyMessage::create();
        $message->setSenderEmail('msg@example.org');
        $message->setSenderName('Message Sender');
        $template = new TransactionalEmailTemplate();

        $factory = new EmailTemplateFactory('system@example.org', 'System', $this->managerReturning($message, $template));

        $body = $factory->createFromMessage($message)->getBody();

        self::assertSame('msg@example.org', $body['message']['from_email']);
        self::assertSame('Message Sender', $body['message']['from_name']);
    }

    public function testFallsBackToSystemSenderWhenNothingSet(): void
    {
        $message = DummyMessage::create();

        $factory = new EmailTemplateFactory('system@example.org', 'System', $this->managerReturning($message, null));

        $body = $factory->createFromMessage($message)->getBody();

        self::assertSame('system@example.org', $body['message']['from_email']);
        self::assertSame('System', $body['message']['from_name']);
    }

    private function managerReturning(Message $message, ?TransactionalEmailTemplate $template): Manager
    {
        $manager = $this->createMock(Manager::class);
        $manager
            ->expects(self::once())
            ->method('findTemplateForMessage')
            ->with($message)
            ->willReturn($template)
        ;

        return $manager;
    }

    private function createSender(string $name, string $email): EmailSender
    {
        $sender = new EmailSender();
        $sender->name = $name;
        $sender->email = $email;

        return $sender;
    }
}
