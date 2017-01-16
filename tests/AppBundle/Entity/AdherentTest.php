<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\ActivationKey;
use AppBundle\Entity\Adherent;
use AppBundle\Exception\AdherentAlreadyEnabledException;
use AppBundle\Membership\ActivityPositions;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\UuidInterface;

class AdherentTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $adherent = $this->createAdherent();

        $this->assertInstanceOf(UuidInterface::class, $adherent->getUuid());
        $this->assertInstanceOf(PhoneNumber::class, $adherent->getPhone());
        $this->assertFalse($adherent->isEnabled());
        $this->assertNull($adherent->getSalt());
        $this->assertNull($adherent->getLastLoggedAt());
        $this->assertSame(['ROLE_ADHERENT'], $adherent->getRoles());
        $this->assertNull($adherent->eraseCredentials());
        $this->assertSame('john.smith@example.org', $adherent->getUsername());
        $this->assertSame('john.smith@example.org', $adherent->getEmailAddress());
        $this->assertSame('super-password', $adherent->getPassword());
        $this->assertSame('male', $adherent->getGender());
        $this->assertSame('John', $adherent->getFirstName());
        $this->assertSame('Smith', $adherent->getLastName());
        $this->assertSame('John Smith', $adherent->getFullName());
        $this->assertSame('FR', $adherent->getCountry());
        $this->assertSame('92 bld du Général Leclerc', $adherent->getAddress());
        $this->assertSame('92110', $adherent->getPostalCode());
        $this->assertSame('92110-92024', $adherent->getCity());
        $this->assertEquals(new \DateTime('1990-12-12'), $adherent->getBirthdate());
        $this->assertSame(ActivityPositions::STUDENT, $adherent->getPosition());
    }

    public function testActivateAdherentAccount()
    {
        $adherent = $this->createAdherent();
        $activationKey = ActivationKey::generate(clone $adherent->getUuid());

        $this->assertFalse($adherent->isEnabled());
        $this->assertNull($adherent->getActivatedAt());
        $this->assertNull($activationKey->getUsageDate());

        $adherent->activate($activationKey);

        $this->assertTrue($adherent->isEnabled());
        $this->assertInstanceOf(\DateTimeImmutable::class, $adherent->getActivatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $activationKey->getUsageDate());
    }

    public function testActivateAdherentAccountTwice()
    {
        $adherent = $this->createAdherent();
        $activationKey = ActivationKey::generate(clone $adherent->getUuid());
        $adherent->activate($activationKey);

        try {
            $adherent->activate($activationKey);
            $this->fail('Adherent account cannot be enabled more than once.');
        } catch (AdherentAlreadyEnabledException $exception) {
        }
    }

    public function testAuthenticateAdherentAccount()
    {
        $adherent = $this->createAdherent();
        $this->assertNull($adherent->getLastLoggedAt());

        $adherent->recordLastLoginTime('2016-01-01 13:30:00');
        $this->assertInstanceOf(\DateTimeImmutable::class, $adherent->getLastLoggedAt());
        $this->assertEquals(new \DateTimeImmutable('2016-01-01 13:30:00'), $adherent->getLastLoggedAt());
    }

    private function createAdherent()
    {
        $phone = new PhoneNumber();
        $phone->setCountryCode('FR');
        $phone->setNationalNumber('0140998211');

        return new Adherent(
            Adherent::createUuid('john.smith@example.org'),
            'john.smith@example.org',
            'super-password',
            'male',
            'John',
            'Smith',
            new \DateTime('1990-12-12'),
            ActivityPositions::STUDENT,
            'FR',
            '92 bld du Général Leclerc',
            '92110-92024',
            '92110',
            $phone
        );
    }
}
