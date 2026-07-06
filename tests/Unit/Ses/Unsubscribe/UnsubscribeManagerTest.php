<?php

declare(strict_types=1);

namespace Tests\App\Unit\Ses\Unsubscribe;

use App\Entity\Adherent;
use App\History\UserActionHistoryHandler;
use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
use App\Repository\AdherentMessageRepository;
use App\Repository\AdherentRepository;
use App\Ses\Unsubscribe\UnsubscribeManager;
use App\Subscription\SubscriptionHandler;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\JWT;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class UnsubscribeManagerTest extends TestCase
{
    // HS256 requires a key of at least 256 bits (32 bytes).
    private const SECRET = 'unit-test-secret-0123456789abcdef';
    private const UUID = '6f1a2b3c-4d5e-6f70-8192-a3b4c5d6e7f8';
    private const MESSAGE_UUID = '22222222-2222-4222-8222-222222222222';

    public function testResolveWithForgedTokenReturnsNull(): void
    {
        // Signed with a different secret: signature verification must fail, repository never hit.
        $forged = JWT::encode(['uuid' => self::UUID], 'a-different-secret-0123456789abcdef', 'HS256');

        $repository = $this->createMock(AdherentRepository::class);
        $repository->expects(self::never())->method('findOneBy');

        self::assertNull($this->manager($repository)->resolve($forged));
    }

    public function testResolveWithMalformedTokenReturnsNull(): void
    {
        $repository = $this->createMock(AdherentRepository::class);
        $repository->expects(self::never())->method('findOneBy');

        self::assertNull($this->manager($repository)->resolve('garbage'));
    }

    public function testResolveWithNonUuidPayloadReturnsNull(): void
    {
        // Validly signed, but the payload is not a UUID: must be rejected before any DB lookup.
        $token = JWT::encode(['uuid' => 'not-a-uuid'], self::SECRET, 'HS256');

        $repository = $this->createMock(AdherentRepository::class);
        $repository->expects(self::never())->method('findOneBy');

        self::assertNull($this->manager($repository)->resolve($token));
    }

    public function testResolveReadsMemberIdFromToken(): void
    {
        $token = JWT::encode(['uuid' => self::UUID, 'member_id' => 42], self::SECRET, 'HS256');

        $context = $this->manager($this->repositoryReturningAdherent())->resolve($token);

        self::assertNotNull($context);
        self::assertSame(42, $context->memberId);
    }

    public function testResolveIgnoresNonIntMemberId(): void
    {
        // A member_id of the wrong type is treated as absent, never breaking the opt-out.
        $token = JWT::encode(['uuid' => self::UUID, 'member_id' => 'not-an-int'], self::SECRET, 'HS256');

        $context = $this->manager($this->repositoryReturningAdherent())->resolve($token);

        self::assertNotNull($context);
        self::assertNull($context->memberId);
    }

    public function testResolveWithoutMemberIdYieldsNullMemberId(): void
    {
        $token = JWT::encode(['uuid' => self::UUID], self::SECRET, 'HS256');

        $context = $this->manager($this->repositoryReturningAdherent())->resolve($token);

        self::assertNotNull($context);
        self::assertNull($context->memberId);
        self::assertNull($context->messageUuid);
    }

    public function testResolveReadsMessageUuidFromToken(): void
    {
        $token = JWT::encode(['uuid' => self::UUID, 'message_uuid' => self::MESSAGE_UUID], self::SECRET, 'HS256');

        $context = $this->manager($this->repositoryReturningAdherent())->resolve($token);

        self::assertNotNull($context);
        self::assertSame(self::MESSAGE_UUID, $context->messageUuid);
    }

    public function testResolveIgnoresNonUuidMessageUuid(): void
    {
        // A message_uuid that is not a valid UUID is treated as absent, never breaking the opt-out.
        $token = JWT::encode(['uuid' => self::UUID, 'message_uuid' => 'not-a-uuid'], self::SECRET, 'HS256');

        $context = $this->manager($this->repositoryReturningAdherent())->resolve($token);

        self::assertNotNull($context);
        self::assertNull($context->messageUuid);
    }

    private function repositoryReturningAdherent(): AdherentRepository
    {
        $repository = $this->createStub(AdherentRepository::class);
        $repository->method('findOneBy')->willReturn($this->createStub(Adherent::class));

        return $repository;
    }

    private function manager(AdherentRepository $repository): UnsubscribeManager
    {
        return new UnsubscribeManager(
            $this->createStub(SubscriptionHandler::class),
            $repository,
            $this->createStub(MailchimpStaticSegmentMemberRepository::class),
            $this->createStub(AdherentMessageRepository::class),
            $this->createStub(UserActionHistoryHandler::class),
            $this->createStub(EntityManagerInterface::class),
            new NullLogger(),
            self::SECRET,
        );
    }
}
