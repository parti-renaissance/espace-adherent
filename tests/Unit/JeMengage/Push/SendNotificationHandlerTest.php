<?php

declare(strict_types=1);

namespace Tests\App\Unit\JeMengage\Push;

use App\Entity\NotificationObjectInterface;
use App\Entity\PushNotification;
use App\Firebase\Notification\PushChunkNotification;
use App\JeMengage\Push\Command\SendNotificationCommandInterface;
use App\JeMengage\Push\Command\SendPushChunkCommand;
use App\JeMengage\Push\NotificationFactory;
use App\JeMengage\Push\SendNotificationHandler;
use App\JeMengage\Push\TokenProviderResolver;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

final class SendNotificationHandlerTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private NotificationFactory&MockObject $notificationFactory;
    private TokenProviderResolver&MockObject $tokenProviderResolver;
    private MessageBusInterface&MockObject $bus;
    private SendNotificationHandler $handler;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->notificationFactory = $this->createMock(NotificationFactory::class);
        $this->tokenProviderResolver = $this->createMock(TokenProviderResolver::class);
        $this->bus = $this->createMock(MessageBusInterface::class);

        $this->handler = new SendNotificationHandler(
            $this->entityManager,
            $this->notificationFactory,
            $this->tokenProviderResolver,
            $this->bus,
        );
    }

    public function testInvokeWithTokensDispatchesChunkCommands(): void
    {
        $command = $this->createCommand();
        $object = $this->mockObjectFound($command);
        $object->method('isNotificationEnabled')->with($command)->willReturn(true);

        $notification = $this->mockNotification($object, $command);
        $this->mockResolverTokens($notification, $object, $command, array_map(
            function (int $i): string { return 'token-'.$i; },
            range(1, 600)
        ));

        $this->bus
            ->expects(self::exactly(2))
            ->method('dispatch')
            ->with(self::isInstanceOf(SendPushChunkCommand::class))
            ->willReturnCallback(function (SendPushChunkCommand $cmd): Envelope {
                return new Envelope($cmd);
            })
        ;

        ($this->handler)($command);
    }

    public function testInvokeWithTokensChunkCommandContainsCorrectData(): void
    {
        $command = $this->createCommand();
        $object = $this->mockObjectFound($command);
        $object->method('isNotificationEnabled')->with($command)->willReturn(true);

        $notification = $this->mockNotification($object, $command);
        $this->mockResolverTokens($notification, $object, $command, ['token-a', 'token-b']);

        $dispatched = null;
        $this->bus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(SendPushChunkCommand::class))
            ->willReturnCallback(function (SendPushChunkCommand $cmd) use (&$dispatched): Envelope {
                $dispatched = $cmd;

                return new Envelope($cmd);
            })
        ;

        ($this->handler)($command);

        self::assertSame('PushChunkNotification', $dispatched->notificationClassName);
        self::assertSame('Test Title', $dispatched->title);
        self::assertSame('Test Body', $dispatched->body);
        self::assertSame('test_scope', $dispatched->scope);
        self::assertSame(['key' => 'value'], $dispatched->data);
        self::assertSame(['token-a', 'token-b'], $dispatched->tokens);
        self::assertNotNull($dispatched->pushNotificationUuid, 'Chunk command should carry pushNotificationUuid');
    }

    public function testInvokeWithTokensPersistsPushNotification(): void
    {
        $command = $this->createCommand();
        $object = $this->mockObjectFound($command);
        $object->method('isNotificationEnabled')->with($command)->willReturn(true);

        $notification = $this->mockNotification($object, $command);
        $this->mockResolverTokens($notification, $object, $command, ['token-1']);

        $this->bus->method('dispatch')->willReturnCallback(function (SendPushChunkCommand $cmd): Envelope {
            return new Envelope($cmd);
        });

        $this->entityManager
            ->expects(self::once())
            ->method('persist')
            ->with(self::isInstanceOf(PushNotification::class))
        ;

        ($this->handler)($command);
    }

    public function testInvokeWithNoTokensDoesNotDispatch(): void
    {
        $command = $this->createCommand();
        $object = $this->mockObjectFound($command);
        $object->method('isNotificationEnabled')->with($command)->willReturn(true);

        $notification = $this->mockNotification($object, $command);
        $this->mockResolverTokens($notification, $object, $command, []);

        $this->bus->expects(self::never())->method('dispatch');
        $object->expects(self::never())->method('handleNotificationSent');

        ($this->handler)($command);
    }

    public function testInvokeWithNotificationDisabledReturnsEarly(): void
    {
        $command = $this->createCommand();
        $object = $this->mockObjectFound($command);
        $object->method('isNotificationEnabled')->with($command)->willReturn(false);

        $this->notificationFactory->expects(self::never())->method('create');
        $this->bus->expects(self::never())->method('dispatch');

        ($this->handler)($command);
    }

    public function testInvokeWithObjectNotFoundReturnsEarly(): void
    {
        $command = $this->createCommand();

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('findOneBy')->with(['uuid' => $command->getUuid()])->willReturn(null);
        $this->entityManager->method('getRepository')->with($command->getClass())->willReturn($repository);

        $this->notificationFactory->expects(self::never())->method('create');
        $this->bus->expects(self::never())->method('dispatch');

        ($this->handler)($command);
    }

    public function testInvokeCallsHandleNotificationSent(): void
    {
        $command = $this->createCommand();
        $object = $this->mockObjectFound($command);
        $object->method('isNotificationEnabled')->with($command)->willReturn(true);

        $notification = $this->mockNotification($object, $command);
        $this->mockResolverTokens($notification, $object, $command, ['token-1']);

        $this->bus->method('dispatch')->willReturnCallback(function (SendPushChunkCommand $cmd): Envelope {
            return new Envelope($cmd);
        });

        $object
            ->expects(self::once())
            ->method('handleNotificationSent')
            ->with($command)
        ;

        $this->entityManager->expects(self::exactly(2))->method('flush');

        ($this->handler)($command);
    }

    private function createCommand(): SendNotificationCommandInterface&MockObject
    {
        $command = $this->createMock(SendNotificationCommandInterface::class);
        $command->method('getUuid')->willReturn(Uuid::v4());
        $command->method('getClass')->willReturn('App\Entity\Event\Event');

        return $command;
    }

    private function mockObjectFound(SendNotificationCommandInterface&MockObject $command): NotificationObjectInterface&MockObject
    {
        $object = $this->createMock(NotificationObjectInterface::class);
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('findOneBy')->with(['uuid' => $command->getUuid()])->willReturn($object);
        $this->entityManager->method('getRepository')->with($command->getClass())->willReturn($repository);

        return $object;
    }

    private function mockNotification(NotificationObjectInterface&MockObject $object, SendNotificationCommandInterface&MockObject $command): PushChunkNotification
    {
        $notification = new PushChunkNotification(
            'Test Title',
            'Test Body',
            'test_scope',
            ['key' => 'value'],
            'TestNotification',
        );

        $this->notificationFactory
            ->method('create')
            ->with($object, $command)
            ->willReturn($notification)
        ;

        return $notification;
    }

    private function mockResolverTokens(PushChunkNotification $notification, NotificationObjectInterface&MockObject $object, SendNotificationCommandInterface&MockObject $command, array $tokens): void
    {
        $this->tokenProviderResolver
            ->method('getTokens')
            ->with($notification, $object, $command)
            ->willReturn($tokens)
        ;
    }
}
