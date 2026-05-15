<?php

declare(strict_types=1);

namespace Tests\App\Entity;

use App\Exception\AdherentTokenAlreadyUsedException;
use App\Exception\AdherentTokenMismatchException;
use App\ValueObject\SHA1;
use Symfony\Component\Uid\Uuid;
use Tests\App\AbstractKernelTestCase;

abstract class AbstractAdherentTokenTestCase extends AbstractKernelTestCase
{
    protected $tokenClass;

    public function testCannotCreateExpiredToken(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $adherent = $this->createAdherent();

        $this->generateToken($adherent, '-10 minutes');
    }

    public function testCannotActivateKeyForAnotherAdherent(): void
    {
        $this->expectException(AdherentTokenMismatchException::class);
        $adherent1 = $this->createAdherent('john.smith@example.org');
        $adherent2 = $this->createAdherent('jane.doe@example.com');

        $token = $this->generateToken($adherent1);

        $token->consume($adherent2);
    }

    public function testCannotActivateSameAdherentActivationTokenTwice(): void
    {
        $this->expectException(AdherentTokenAlreadyUsedException::class);
        $adherent = $this->createAdherent();

        $token = $this->generateToken($adherent);

        $token->consume($adherent);

        $token->consume($adherent);
    }

    public function testConsumeTokenIsSuccessful(): void
    {
        $adherent = $this->createAdherent();
        $token = $this->generateToken($adherent);

        $this->assertInstanceOf($this->tokenClass, $token);
        $this->assertInstanceOf(Uuid::class, $token->getUuid());
        $this->assertInstanceOf(Uuid::class, $token->getAdherentUuid());
        $this->assertInstanceOf(SHA1::class, $token->getValue());
        $this->assertNotEquals($adherent->getUuid(), $token->getUuid());
        $this->assertEquals($adherent->getUuid(), $token->getAdherentUuid());
        $this->assertNull($token->getId());
        $this->assertNull($token->getUsageDate());

        $token->consume($adherent);

        $this->assertInstanceOf(\DateTime::class, $token->getUsageDate());
    }

    private function generateToken($adherent, string $lifetime = '+1 day')
    {
        return \call_user_func([$this->tokenClass, 'generate'], $adherent, $lifetime);
    }
}
