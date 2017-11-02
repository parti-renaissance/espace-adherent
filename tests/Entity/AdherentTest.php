<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentActivationToken;
use AppBundle\Entity\PostAddress;
use AppBundle\Geocoder\Coordinates;
use AppBundle\Membership\ActivityPositions;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;
use Tests\AppBundle\TestHelperTrait;

class AdherentTest extends TestCase
{
    use TestHelperTrait;

    public function testConstruct()
    {
        $adherent = $this->createAdherent();

        $this->assertInstanceOf(UuidInterface::class, $adherent->getUuid());
        $this->assertInstanceOf(PhoneNumber::class, $adherent->getPhone());
        $this->assertFalse($adherent->isEnabled());
        $this->assertNull($adherent->getSalt());
        $this->assertNull($adherent->getLastLoggedAt());
        $this->assertSame(['ROLE_USER', 'ROLE_ADHERENT'], $adherent->getRoles());
        $this->assertSame('john.smith@example.org', $adherent->getUsername());
        $this->assertSame('john.smith@example.org', $adherent->getEmailAddress());
        $this->assertSame('male', $adherent->getGender());
        $this->assertSame('John', $adherent->getFirstName());
        $this->assertSame('Smith', $adherent->getLastName());
        $this->assertSame('John Smith', $adherent->getFullName());
        $this->assertSame('FR', $adherent->getCountry());
        $this->assertSame('92 bld du Général Leclerc', $adherent->getAddress());
        $this->assertSame('92110', $adherent->getPostalCode());
        $this->assertSame('92110-92024', $adherent->getCity());
        $this->assertSame('92024', $adherent->getInseeCode());
        $this->assertEquals(new \DateTime('1990-12-12'), $adherent->getBirthdate());
        $this->assertSame(ActivityPositions::STUDENT, $adherent->getPosition());
        $this->assertNull($adherent->getLatitude());
        $this->assertNull($adherent->getLongitude());
    }

    public function testAdherentsAreEqual()
    {
        $adherent1 = $this->createAdherent('john.smith@example.org');
        $adherent2 = $this->createAdherent('john.smith@example.org');
        $adherent3 = $this->createAdherent('foo.bar@example.org');

        $this->assertTrue($adherent1->equals($adherent2));
        $this->assertTrue($adherent2->equals($adherent1));

        $this->assertFalse($adherent1->equals($adherent3));
        $this->assertFalse($adherent3->equals($adherent2));
    }

    public function testGeoAddressAndCoordinates()
    {
        $adherent = $this->createAdherent();
        $adherent->updateCoordinates(new Coordinates(12.456323, 89.735324));

        $this->assertSame('92 bld du Général Leclerc, 92110 Clichy, FR', $adherent->getGeocodableAddress());
        $this->assertSame(12.456323, $adherent->getLatitude());
        $this->assertSame(89.735324, $adherent->getLongitude());
    }

    public function testActivateAdherentAccount()
    {
        $adherent = $this->createAdherent();
        $activationToken = AdherentActivationToken::generate($adherent);

        $this->assertFalse($adherent->isEnabled());
        $this->assertNull($adherent->getActivatedAt());
        $this->assertNull($activationToken->getUsageDate());

        $adherent->activate($activationToken);

        $this->assertTrue($adherent->isEnabled());
        $this->assertInstanceOf(\DateTime::class, $adherent->getActivatedAt());
        $this->assertInstanceOf(\DateTime::class, $activationToken->getUsageDate());
    }

    /**
     * @expectedException \AppBundle\Exception\AdherentAlreadyEnabledException
     */
    public function testActivateAdherentAccountTwice()
    {
        $adherent = $this->createAdherent();
        $activationToken = AdherentActivationToken::generate($adherent);

        $adherent->activate($activationToken);

        $adherent->activate($activationToken);
    }

    public function testAuthenticateAdherentAccount()
    {
        $adherent = $this->createAdherent();
        $this->assertNull($adherent->getLastLoggedAt());

        $adherent->recordLastLoginTime('2016-01-01 13:30:00');
        $this->assertInstanceOf(\DateTime::class, $adherent->getLastLoggedAt());
        $this->assertEquals(new \DateTime('2016-01-01 13:30:00'), $adherent->getLastLoggedAt());
    }

    public function testUserWithLegislativeCandidateRole()
    {
        $adherent = $this->createAdherent();
        $this->assertFalse(in_array('ROLE_LEGISLATIVE_CANDIDATE', $adherent->getRoles()));

        $adherent = $this->createAdherent('john.smith@en-marche.fr');
        $this->assertTrue(in_array('ROLE_LEGISLATIVE_CANDIDATE', $adherent->getRoles()));
    }

    private function createAdherent($email = 'john.smith@example.org'): Adherent
    {
        $phone = new PhoneNumber();
        $phone->setCountryCode('FR');
        $phone->setNationalNumber('0140998211');

        return new Adherent(
            Adherent::createUuid($email),
            $email,
            'male',
            'John',
            'Smith',
            new \DateTime('1990-12-12'),
            ActivityPositions::STUDENT,
            PostAddress::createFrenchAddress('92 bld du Général Leclerc', '92110-92024'),
            $phone
        );
    }
}
