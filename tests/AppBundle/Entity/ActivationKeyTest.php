<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\ActivationKey;
use AppBundle\Exception\ActivationKeyAlreadyUsedException;
use AppBundle\ValueObject\SHA1;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class ActivationKeyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCannotCreateExpiredActivationKey()
    {
        ActivationKey::generate(Uuid::fromString('6fe43ed7-b9f0-4f68-b589-0ff17729b156'), '-10 minutes');
    }

    public function testCannotActivateSameKeyTwice()
    {
        $adherent = Uuid::fromString('6fe43ed7-b9f0-4f68-b589-0ff17729b156');

        $key = ActivationKey::generate($adherent);
        $key->activate($adherent);

        try {
            $key->activate($adherent);
            $this->fail('Activation key cannot be activated more than once.');
        } catch (ActivationKeyAlreadyUsedException $e) {
        }
    }

    /**
     * @expectedException \AppBundle\Exception\ActivationKeyMismatchException
     */
    public function testCannotActivateKeyForAnotherAdherent()
    {
        $key = ActivationKey::generate(Uuid::fromString('6fe43ed7-b9f0-4f68-b589-0ff17729b156'));
        $key->activate(Uuid::fromString('d2d448c5-d3d7-425c-bd78-34ba0ecaccc1'));
    }

    public function testUseActivationKeyIsSuccessful()
    {
        $adherent = Uuid::fromString('6fe43ed7-b9f0-4f68-b589-0ff17729b156');
        $key = ActivationKey::generate($adherent);

        $this->assertInstanceOf(ActivationKey::class, $key);
        $this->assertInstanceOf(UuidInterface::class, $key->getUuid());
        $this->assertInstanceOf(UuidInterface::class, $key->getAdherentUuid());
        $this->assertInstanceOf(SHA1::class, $key->getToken());
        $this->assertNotEquals($adherent, $key->getUuid());
        $this->assertSame($adherent, $key->getAdherentUuid());
        $this->assertNull($key->getId());
        $this->assertNull($key->getUsageDate());

        $key->activate($adherent);
        $this->assertInstanceOf(\DateTimeImmutable::class, $key->getUsageDate());
    }
}
