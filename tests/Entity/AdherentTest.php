<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentActivationToken;
use AppBundle\Entity\BoardMember\BoardMember;
use AppBundle\Entity\CommitteeMembership;
use AppBundle\Entity\PostAddress;
use AppBundle\Entity\ReferentTag;
use AppBundle\Geocoder\Coordinates;
use AppBundle\Membership\ActivityPositions;
use Doctrine\Common\Collections\ArrayCollection;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\TestCase;
use Tests\AppBundle\TestHelperTrait;

class AdherentTest extends TestCase
{
    use TestHelperTrait;

    public function testConstruct(): void
    {
        $adherent = $this->createAdherent();

        $this->assertInstanceOf(PhoneNumber::class, $adherent->getPhone());
        $this->assertFalse($adherent->isEnabled());
        $this->assertNull($adherent->getSalt());
        $this->assertNull($adherent->getLastLoggedAt());
        $this->assertSame(['ROLE_USER'], $adherent->getRoles());
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
        $this->assertSame('92024', $adherent->getInseeCode());
        $this->assertEquals(new \DateTime('1990-12-12'), $adherent->getBirthdate());
        $this->assertSame(ActivityPositions::STUDENT, $adherent->getPosition());
        $this->assertNull($adherent->getLatitude());
        $this->assertNull($adherent->getLongitude());
        $this->assertSame(10, $adherent->getCitizenProjectCreationEmailSubscriptionRadius());
    }

    public function testCitizenProjectCreationEmailSubscriptionRadiusIs10ByDefault(): void
    {
        $this->assertSame(10, $this->createAdherent()->getCitizenProjectCreationEmailSubscriptionRadius());
    }

    public function testAdherentsAreEqual(): void
    {
        $adherent1 = $this->createAdherent('john.smith@example.org');
        $adherent2 = $this->createAdherent('john.smith@example.org');
        $adherent3 = $this->createAdherent('foo.bar@example.org');

        $this->assertTrue($adherent1->equals($adherent2));
        $this->assertTrue($adherent2->equals($adherent1));

        $this->assertFalse($adherent1->equals($adherent3));
        $this->assertFalse($adherent3->equals($adherent2));
    }

    public function testGeoAddressAndCoordinates(): void
    {
        $adherent = $this->createAdherent();
        $adherent->updateCoordinates(new Coordinates(12.456323, 89.735324));

        $this->assertSame('92 bld du Général Leclerc, 92110 Clichy, FR', $adherent->getGeocodableAddress());
        $this->assertSame(12.456323, $adherent->getLatitude());
        $this->assertSame(89.735324, $adherent->getLongitude());
    }

    public function testActivateAdherentAccount(): void
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
    public function testActivateAdherentAccountTwice(): void
    {
        $adherent = $this->createAdherent();
        $activationToken = AdherentActivationToken::generate($adherent);

        $adherent->activate($activationToken);

        $adherent->activate($activationToken);
    }

    public function testAuthenticateAdherentAccount(): void
    {
        $adherent = $this->createAdherent();
        $this->assertNull($adherent->getLastLoggedAt());

        $adherent->recordLastLoginTime('2016-01-01 13:30:00');
        $this->assertInstanceOf(\DateTime::class, $adherent->getLastLoggedAt());
        $this->assertEquals(new \DateTime('2016-01-01 13:30:00'), $adherent->getLastLoggedAt());
    }

    public function testUserWithLegislativeCandidateRole(): void
    {
        $adherent = $this->createAdherent();
        $this->assertNotContains('ROLE_LEGISLATIVE_CANDIDATE', $adherent->getRoles());

        $adherent = $this->createAdherent('john.smith@en-marche.fr');
        $this->assertNotContains('ROLE_LEGISLATIVE_CANDIDATE', $adherent->getRoles());
    }

    public function testIsBasicAdherent(): void
    {
        // User
        $adherent = $this->createAdherent();

        $this->assertFalse($adherent->isBasicAdherent());

        // Basic
        $adherent->join();

        $this->assertTrue($adherent->isBasicAdherent());

        // Host
        $adherent = $this->createAdherent();
        $adherent->join();
        $memberships = $adherent->getMemberships();

        $membership = $this->createMock(CommitteeMembership::class);
        $membership->expects($this->once())->method('canHostCommittee')->willReturn(true);
        $memberships->add($membership);

        $this->assertFalse($adherent->isBasicAdherent());

        // Referent
        $adherent = $this->createAdherent();
        $adherent->setReferentInfo([new ReferentTag('06')], -1.6743, 48.112);

        $this->assertFalse($adherent->isBasicAdherent());

        // BoardMember
        $adherent = $this->createAdherent();
        $adherent->setBoardMember(BoardMember::AREA_ABROAD, new ArrayCollection());

        $this->assertFalse($adherent->isBasicAdherent());
    }

    private function createAdherent($email = 'john.smith@example.org'): Adherent
    {
        $phone = new PhoneNumber();
        $phone->setCountryCode('FR');
        $phone->setNationalNumber('0140998211');

        return Adherent::create(
            Adherent::createUuid($email),
            $email,
            'super-password',
            'male',
            'John',
            'Smith',
            new \DateTime('1990-12-12'),
            ActivityPositions::STUDENT,
            PostAddress::createFrenchAddress('92 bld du Général Leclerc', '92110-92024'),
            $phone
        );
    }

    protected function tearDown()
    {
        $this->cleanupContainer($this->container);

        $this->container = null;
        $this->adherents = null;

        parent::tearDown();
    }
}
