<?php

namespace Tests\App\Entity;

use App\ValueObject\SHA1;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;
use Tests\App\TestHelperTrait;

abstract class AbstractAdherentTokenTest extends TestCase
{
    use TestHelperTrait;

    protected $tokenClass;

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCannotCreateExpiredToken()
    {
        $adherent = $this->createAdherent();

        $this->generateToken($adherent, '-10 minutes');
    }

    /**
     * @expectedException \App\Exception\AdherentTokenMismatchException
     */
    public function testCannotActivateKeyForAnotherAdherent()
    {
        $adherent1 = $this->createAdherent('john.smith@example.org');
        $adherent2 = $this->createAdherent('jane.doe@example.com');

        $token = $this->generateToken($adherent1);

        $token->consume($adherent2);
    }

    /**
     * @expectedException \App\Exception\AdherentTokenAlreadyUsedException
     */
    public function testCannotActivateSameAdherentActivationTokenTwice()
    {
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

    protected function tearDown()
    {
        $this->cleanupContainer($this->container);

        $this->container = null;
        $this->adherents = null;

        parent::tearDown();
    }
}
