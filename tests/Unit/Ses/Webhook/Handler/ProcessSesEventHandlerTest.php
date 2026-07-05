<?php

declare(strict_types=1);

namespace Tests\App\Unit\Ses\Webhook\Handler;

use App\Entity\Ses\SesEvent;
use App\Ses\Webhook\Command\ProcessSesEventCommand;
use App\Ses\Webhook\Handler\ProcessSesEventHandler;
use App\Ses\Webhook\SesEventDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

/**
 * The handler reloads the stored event from ses_event (source of truth) and routes its payload. When no row
 * exists for the id (e.g. purged before a replay), it is a no-op. The real JSON round-trip + routing is
 * covered end-to-end by SesNotificationControllerTest; here the found/not-found branching is isolated.
 */
final class ProcessSesEventHandlerTest extends TestCase
{
    public function testRoutesStoredPayload(): void
    {
        $payload = ['MessageId' => 'sns-1', 'Message' => '{"eventType":"Delivery"}'];

        $event = new SesEvent();
        $event->payload = $payload;

        $router = $this->createMock(SesEventDispatcher::class);
        $router
            ->expects(self::once())
            ->method('dispatch')
            ->with($payload)
        ;

        $this->handler($this->entityManagerReturning('sns-1', $event), $router)(new ProcessSesEventCommand('sns-1'));
    }

    public function testDoesNothingWhenEventNotFound(): void
    {
        $router = $this->createMock(SesEventDispatcher::class);
        $router
            ->expects(self::never())
            ->method('dispatch')
        ;

        $this->handler($this->entityManagerReturning('sns-missing', null), $router)(new ProcessSesEventCommand('sns-missing'));
    }

    private function handler(EntityManagerInterface $entityManager, SesEventDispatcher $router): ProcessSesEventHandler
    {
        return new ProcessSesEventHandler($entityManager, $router);
    }

    private function entityManagerReturning(string $snsMessageId, ?SesEvent $event): EntityManagerInterface
    {
        $repository = $this->createMock(EntityRepository::class);
        $repository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['snsMessageId' => $snsMessageId])
            ->willReturn($event)
        ;

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects(self::once())
            ->method('getRepository')
            ->with(SesEvent::class)
            ->willReturn($repository)
        ;

        return $entityManager;
    }
}
