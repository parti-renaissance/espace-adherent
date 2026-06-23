<?php

declare(strict_types=1);

namespace Tests\App\Unit\Ses\Unsubscribe;

use App\Repository\AdherentRepository;
use App\Ses\Unsubscribe\UnsubscribeManager;
use App\Subscription\SubscriptionHandler;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\JWT;
use PHPUnit\Framework\TestCase;

class UnsubscribeManagerTest extends TestCase
{
    // HS256 requires a key of at least 256 bits (32 bytes).
    private const SECRET = 'unit-test-secret-0123456789abcdef';

    public function testResolveAdherentWithForgedTokenReturnsNull(): void
    {
        // Signed with a different secret: signature verification must fail, repository never hit.
        $forged = JWT::encode(['uuid' => '6f1a2b3c-4d5e-6f70-8192-a3b4c5d6e7f8'], 'a-different-secret-0123456789abcdef', 'HS256');

        $repository = $this->createMock(AdherentRepository::class);
        $repository->expects(self::never())->method('findOneBy');

        self::assertNull($this->manager($repository)->resolveAdherent($forged));
    }

    public function testResolveAdherentWithMalformedTokenReturnsNull(): void
    {
        $repository = $this->createMock(AdherentRepository::class);
        $repository->expects(self::never())->method('findOneBy');

        self::assertNull($this->manager($repository)->resolveAdherent('garbage'));
    }

    public function testResolveAdherentWithNonUuidPayloadReturnsNull(): void
    {
        // Validly signed, but the payload is not a UUID: must be rejected before any DB lookup.
        $token = JWT::encode(['uuid' => 'not-a-uuid'], self::SECRET, 'HS256');

        $repository = $this->createMock(AdherentRepository::class);
        $repository->expects(self::never())->method('findOneBy');

        self::assertNull($this->manager($repository)->resolveAdherent($token));
    }

    private function manager(AdherentRepository $repository): UnsubscribeManager
    {
        return new UnsubscribeManager(
            $this->createStub(SubscriptionHandler::class),
            $repository,
            $this->createStub(EntityManagerInterface::class),
            self::SECRET,
        );
    }
}
