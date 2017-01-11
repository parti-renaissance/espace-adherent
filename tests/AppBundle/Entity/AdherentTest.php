<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Adherent;
use AppBundle\Membership\ActivityPositions;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\UuidInterface;

class AdherentTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $phone = new PhoneNumber();
        $phone->setCountryCode('FR');
        $phone->setNationalNumber('0140998211');

        $adherent = new Adherent(
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

        $this->assertInstanceOf(UuidInterface::class, $adherent->getUuid());
        $this->assertSame($phone, $adherent->getPhone());
        $this->assertNull($adherent->getSalt());
        $this->assertSame(['ROLE_ADHERENT'], $adherent->getRoles());
        $this->assertNull($adherent->eraseCredentials());
        $this->assertSame('john.smith@example.org', $adherent->getUsername());
        $this->assertSame('super-password', $adherent->getPassword());
        $this->assertSame('male', $adherent->getGender());
        $this->assertSame('John', $adherent->getFirstName());
        $this->assertSame('Smith', $adherent->getLastName());
        $this->assertSame('FR', $adherent->getCountry());
        $this->assertSame('92 bld du Général Leclerc', $adherent->getAddress());
        $this->assertSame('92110', $adherent->getPostalCode());
        $this->assertSame('92110-92024', $adherent->getCity());
        $this->assertEquals(new \DateTime('1990-12-12'), $adherent->getBirthdate());
        $this->assertSame(ActivityPositions::STUDENT, $adherent->getPosition());
    }
}
