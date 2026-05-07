<?php

declare(strict_types=1);

namespace Tests\App\Unit\AdherentMessage\Handler;

use App\AdherentMessage\AdherentMessageManager;
use App\AdherentMessage\Command\SendAdherentMessageCommand;
use App\AdherentMessage\Handler\SendAdherentMessageCommandHandler;
use App\Entity\AdherentMessage\AdherentMessage;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class SendAdherentMessageCommandHandlerTest extends TestCase
{
    public function testInvokeMessageNotFoundLogsWarningAndReturns(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())
            ->method('find')
            ->with(AdherentMessage::class, 42)
            ->willReturn(null)
        ;

        $manager = $this->createMock(AdherentMessageManager::class);
        $manager->expects(self::never())->method('send');
        $manager->expects(self::never())->method('getRecipients');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('warning')
            ->with('[SendAdherentMessage] AdherentMessage not found', ['id' => 42])
        ;
        $logger->expects(self::never())->method('error');

        $handler = new SendAdherentMessageCommandHandler($em, $manager, $logger);
        $handler(new SendAdherentMessageCommand(42));
    }

    public function testInvokeAlreadySentReturnsWithoutAction(): void
    {
        $message = new AdherentMessage();
        $this->setEntityId($message, 100);
        $message->markAsSent();

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())
            ->method('find')
            ->with(AdherentMessage::class, 100)
            ->willReturn($message)
        ;

        $manager = $this->createMock(AdherentMessageManager::class);
        $manager->expects(self::never())->method('send');
        $manager->expects(self::never())->method('getRecipients');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');
        $logger->expects(self::never())->method('warning');

        $handler = new SendAdherentMessageCommandHandler($em, $manager, $logger);
        $handler(new SendAdherentMessageCommand(100));
    }

    public function testInvokeSuccessCallsManagerSend(): void
    {
        $message = new AdherentMessage();
        $this->setEntityId($message, 100);
        $recipients = ['recipient-1@example.com', 'recipient-2@example.com'];

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())
            ->method('find')
            ->with(AdherentMessage::class, 100)
            ->willReturn($message)
        ;

        $manager = $this->createMock(AdherentMessageManager::class);
        $manager->expects(self::once())
            ->method('getRecipients')
            ->with($message)
            ->willReturn($recipients)
        ;
        $manager->expects(self::once())
            ->method('send')
            ->with($message, $recipients)
        ;

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');
        $logger->expects(self::never())->method('warning');

        $handler = new SendAdherentMessageCommandHandler($em, $manager, $logger);
        $handler(new SendAdherentMessageCommand(100));
    }

    public function testInvokeManagerSendFailureLogsErrorAndRethrows(): void
    {
        $uuid = Uuid::fromString('11111111-1111-1111-1111-111111111111');
        $message = new AdherentMessage($uuid);
        $this->setEntityId($message, 100);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())
            ->method('find')
            ->with(AdherentMessage::class, 100)
            ->willReturn($message)
        ;

        $manager = $this->createMock(AdherentMessageManager::class);
        $manager->expects(self::once())
            ->method('getRecipients')
            ->with($message)
            ->willReturn([])
        ;
        $manager->expects(self::once())
            ->method('send')
            ->with($message, [])
            ->willThrowException(new \RuntimeException('mailchimp 502'))
        ;

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('error')
            ->with(
                '[SendAdherentMessage] send failed',
                self::callback(function (array $context) use ($uuid): bool {
                    return 100 === $context['message_id']
                        && $uuid->toString() === $context['message_uuid']
                        && $context['exception'] instanceof \RuntimeException
                        && 'mailchimp 502' === $context['exception']->getMessage();
                }),
            )
        ;

        $handler = new SendAdherentMessageCommandHandler($em, $manager, $logger);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('mailchimp 502');

        $handler(new SendAdherentMessageCommand(100));
    }

    private function setEntityId(object $entity, int $id): void
    {
        $reflection = new \ReflectionObject($entity);
        $property = $reflection->getProperty('id');
        $property->setValue($entity, $id);
    }
}
