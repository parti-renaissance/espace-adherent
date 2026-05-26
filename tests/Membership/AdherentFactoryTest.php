<?php

declare(strict_types=1);

namespace Tests\App\Membership;

use App\Address\Address;
use App\Entity\Adherent;
use App\Membership\AdherentFactory;
use App\PublicId\AdherentPublicIdGenerator;
use App\Repository\AdherentRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class AdherentFactoryTest extends TestCase
{
    public function testCreateForSignupProducesPasswordlessPendingAdherent(): void
    {
        $adherent = $this->createFactory()->createForSignup('Contact@Example.COM');

        self::assertTrue($adherent->isPending());
        self::assertSame('contact@example.com', $adherent->getEmailAddress());
        // Deterministic UUID derived from the (lowercased) email.
        self::assertSame(Adherent::createUuid('contact@example.com')->toRfc4122(), $adherent->getUuidAsString());
        // Passwordless: connections happen through the magic link only.
        self::assertNull($adherent->getPassword());
        self::assertSame('', $adherent->getFirstName());
        self::assertSame('', $adherent->getLastName());
    }

    public function testCreateForSignupKeepsProvidedGenderAndName(): void
    {
        $adherent = $this->createFactory()->createForSignup('jane@example.com', 'female', 'Jane', 'Doe');

        self::assertSame('female', $adherent->getGender());
        self::assertSame('Jane', $adherent->getFirstName());
        self::assertSame('Doe', $adherent->getLastName());
    }

    public function testCreateForSignupSetsFlexiblePostAddressFromPartialAddress(): void
    {
        $address = new Address();
        $address->setCountry('FR');
        $address->setPostalCode('75008');
        $address->setCityName('Paris');

        $adherent = $this->createFactory()->createForSignup('zip@example.com', address: $address);

        self::assertSame('75008', $adherent->getPostAddress()->getPostalCode());
        self::assertSame('Paris', $adherent->getPostAddress()->getCityName());
    }

    private function createFactory(): AdherentFactory
    {
        // createForSignup creates a passwordless account, so the hasher is never invoked. It is still
        // requested once in the constructor.
        $hasher = $this->createStub(PasswordHasherInterface::class);

        $hasherFactory = $this->createMock(PasswordHasherFactoryInterface::class);
        $hasherFactory
            ->expects(self::once())
            ->method('getPasswordHasher')
            ->with(Adherent::class)
            ->willReturn($hasher)
        ;

        // AbstractPublicIdGenerator::generate() is final: use a real generator whose repository reports
        // no collision, so it returns a random id offline.
        $adherentRepository = $this->createStub(AdherentRepository::class);
        $adherentRepository->method('publicIdExists')->willReturn(false);
        $publicIdGenerator = new AdherentPublicIdGenerator($adherentRepository);

        return new AdherentFactory($hasherFactory, $publicIdGenerator);
    }
}
