<?php

declare(strict_types=1);

namespace Tests\App\Entity;

use App\Exception\AdherentTokenAlreadyUsedException;
use App\Exception\AdherentTokenMismatchException;
use App\ValueObject\SHA1;
use Ramsey\Uuid\UuidInterface;
use Tests\App\AbstractKernelTestCase;

abstract class AbstractAdherentTokenTestCase extends AbstractKernelTestCase
{
    protected $tokenClass;

    public function testCannotCreateExpiredToken()
    {
        $this->expectException(\InvalidArgumentException::class);
        $adherent = $this->createAdherent();

        $this->generateToken($adherent, '-10 minutes');
    }

    public function testCannotActivateKeyForAnotherAdherent()
    {
        $this->expectException(AdherentTokenMismatchException::class);
        $adherent1 = $this->createAdherent('john.smith@example.org');
        $adherent2 = $this->createAdherent('jane.doe@example.com');

        $token = $this->generateToken($adherent1);

        $token->consume($adherent2);
    }

    public function testCannotActivateSameAdherentActivationTokenTwice()
    {
        $this->expectException(AdherentTokenAlreadyUsedException::class);
        $adherent = $this->createAdherent();

        $token = $this->generateToken($adherent);

        $token->consume($adherent);

        $token->consume($adherent);
    }

    public function testConsumeTokenIsSuccessful()
    {
        $adherent = $this->createAdherent();
        $token = $this->generateToken($adherent);

        $this->assertInstanceOf($this->tokenClass, $token);
        $this->assertInstanceOf(UuidInterface::class, $token->getUuid());
        $this->assertInstanceOf(UuidInterface::class, $token->getAdherentUuid());
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
