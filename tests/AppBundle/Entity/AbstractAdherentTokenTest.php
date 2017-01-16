<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Exception\AdherentTokenAlreadyUsedException;
use AppBundle\ValueObject\SHA1;
use Ramsey\Uuid\UuidInterface;
use Tests\AppBundle\TestHelperTrait;

abstract class AbstractAdherentTokenTest extends \PHPUnit_Framework_TestCase
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
     * @expectedException \AppBundle\Exception\AdherentTokenMismatchException
     */
    public function testCannotActivateKeyForAnotherAdherent()
    {
        $adherent1 = $this->createAdherent('john.smith@example.org');
        $adherent2 = $this->createAdherent('jane.doe@example.com');

        $token = $this->generateToken($adherent1);

        $token->consume($adherent2);
    }

    public function testCannotActivateSameAdherentActivationTokenTwice()
    {
        $adherent = $this->createAdherent();

        $token = $this->generateToken($adherent);

        $token->consume($adherent);

        try {
            $token->consume($adherent);
            $this->fail('Adherent activation token cannot be activated more than once.');
        } catch (AdherentTokenAlreadyUsedException $e) {
        }
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

        $this->assertInstanceOf(\DateTimeImmutable::class, $token->getUsageDate());
    }

    private function generateToken($adherent, string $lifetime = '+1 day')
    {
        return call_user_func([$this->tokenClass, 'generate'], $adherent, $lifetime);
    }
}
