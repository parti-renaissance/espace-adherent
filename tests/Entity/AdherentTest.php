<?php

declare(strict_types=1);

namespace Tests\App\Entity;

use App\Entity\Adherent;
use App\Entity\AdherentActivationToken;
use App\Exception\AdherentAlreadyEnabledException;
use App\Geocoder\Coordinates;
use App\Mailchimp\Contact\ContactStatusEnum;
use App\Membership\ActivityPositionsEnum;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\App\AbstractKernelTestCase;

class AdherentTest extends AbstractKernelTestCase
{
    public function testConstruct(): void
    {
        $adherent = $this->createNewAdherent();

        $this->assertInstanceOf(PhoneNumber::class, $adherent->getPhone());
        $this->assertFalse($adherent->isEnabled());
        $this->assertNull($adherent->getLastLoggedAt());
        $this->assertNull($adherent->getLastLoginGroup());
        $this->assertSame(['ROLE_USER'], $adherent->getRoles());
        $this->assertSame('john.smith@example.org', $adherent->getUserIdentifier());
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
        $this->assertSame(ActivityPositionsEnum::STUDENT, $adherent->getPosition());
        $this->assertNull($adherent->getLatitude());
        $this->assertNull($adherent->getLongitude());
    }

    public function testMarkAsEmailHardBouncedSetsTimestampOnce(): void
    {
        $adherent = $this->createNewAdherent();
        $this->assertFalse($adherent->isEmailHardBounced());

        $adherent->markAsEmailHardBounced();
        $this->assertTrue($adherent->isEmailHardBounced());
        $first = $adherent->emailHardBouncedAt;

        $adherent->markAsEmailHardBounced();
        $this->assertSame($first, $adherent->emailHardBouncedAt, 'idempotent: keeps the first occurrence date');
    }

    public function testMarkAsEmailHardBouncedDoesNotTouchConsentOrSms(): void
    {
        $adherent = $this->createNewAdherent();
        $statusBefore = $adherent->getMailchimpStatus();
        $subscriptionsBefore = $adherent->getSubscriptionTypes();

        $adherent->markAsEmailHardBounced();

        $this->assertSame($statusBefore, $adherent->getMailchimpStatus());
        $this->assertSame($subscriptionsBefore, $adherent->getSubscriptionTypes());
    }

    public function testChangingEmailAddressResetsHardBounce(): void
    {
        $adherent = $this->createNewAdherent();
        $adherent->markAsEmailHardBounced();
        $this->assertTrue($adherent->isEmailHardBounced());

        $adherent->setEmailAddress('a-new-fresh-address@example.org');
        $this->assertFalse($adherent->isEmailHardBounced(), 'a new address gets a fresh chance');
    }

    public function testReassigningSameEmailKeepsHardBounce(): void
    {
        $adherent = $this->createNewAdherent();
        $adherent->markAsEmailHardBounced();

        $adherent->setEmailAddress($adherent->getEmailAddress());
        $this->assertTrue($adherent->isEmailHardBounced(), 'same address must not reset suppression');
    }

    public function testMarkAsEmailComplainedSetsTimestampOnce(): void
    {
        $adherent = $this->createNewAdherent();
        $this->assertFalse($adherent->isEmailComplained());

        $adherent->markAsEmailComplained();
        $this->assertTrue($adherent->isEmailComplained());
        $first = $adherent->emailComplainedAt;

        $adherent->markAsEmailComplained();
        $this->assertSame($first, $adherent->emailComplainedAt, 'idempotent: keeps the first occurrence date');
    }

    public function testCanReceiveEmailWhenSubscribedAndDeliverable(): void
    {
        $adherent = $this->createNewAdherent();

        $this->assertTrue($adherent->isEmailSubscribed());
        $this->assertTrue($adherent->canReceiveEmail());
    }

    public function testCannotReceiveEmailWhenHardBounced(): void
    {
        $adherent = $this->createNewAdherent();
        $adherent->markAsEmailHardBounced();

        $this->assertTrue($adherent->isEmailSubscribed(), 'consent is untouched by a bounce');
        $this->assertFalse($adherent->canReceiveEmail());
    }

    public function testCannotReceiveEmailWhenComplained(): void
    {
        $adherent = $this->createNewAdherent();
        $adherent->markAsEmailComplained();

        $this->assertFalse($adherent->canReceiveEmail());
    }

    public function testCannotReceiveEmailWhenUnsubscribed(): void
    {
        $adherent = $this->createNewAdherent();
        $adherent->setEmailUnsubscribed(true);

        $this->assertFalse($adherent->isEmailSubscribed());
        $this->assertFalse($adherent->canReceiveEmail());
    }

    public function testReSubscribeClearsComplaint(): void
    {
        $adherent = $this->createNewAdherent();
        $adherent->markAsEmailComplained();
        $adherent->setEmailUnsubscribed(true);
        $this->assertTrue($adherent->isEmailComplained());

        $adherent->setEmailUnsubscribed(false);

        $this->assertFalse($adherent->isEmailComplained(), 'invariant: a re-subscribe clears the complaint');
        $this->assertSame(ContactStatusEnum::SUBSCRIBED, $adherent->getMailchimpStatus());
        $this->assertTrue($adherent->canReceiveEmail());
    }

    public function testChangingEmailAddressResetsComplaint(): void
    {
        $adherent = $this->createNewAdherent();
        $adherent->markAsEmailComplained();
        $this->assertTrue($adherent->isEmailComplained());

        $adherent->setEmailAddress('a-new-fresh-address@example.org');
        $this->assertFalse($adherent->isEmailComplained(), 'a new address gets a fresh chance');
    }

    public function testAdherentsAreEqual(): void
    {
        $adherent1 = $this->createNewAdherent('john.smith@example.org');
        $adherent2 = $this->createNewAdherent('john.smith@example.org');
        $adherent3 = $this->createNewAdherent('foo.bar@example.org');

        $this->assertTrue($adherent1->equals($adherent2));
        $this->assertTrue($adherent2->equals($adherent1));

        $this->assertFalse($adherent1->equals($adherent3));
        $this->assertFalse($adherent3->equals($adherent2));
    }

    public function testGeoAddressAndCoordinates(): void
    {
        $adherent = $this->createNewAdherent();
        $adherent->updateCoordinates(new Coordinates(12.456323, 89.735324));

        $this->assertSame('92 bld du Général Leclerc, 92110 Clichy, France', $adherent->getGeocodableAddress());
        $this->assertSame(12.456323, $adherent->getLatitude());
        $this->assertSame(89.735324, $adherent->getLongitude());
    }

    public function testActivateAdherentAccount(): void
    {
        $adherent = $this->createNewAdherent();
        $activationToken = AdherentActivationToken::generate($adherent);

        $this->assertFalse($adherent->isEnabled());
        $this->assertNull($adherent->getActivatedAt());
        $this->assertNull($activationToken->getUsageDate());

        $adherent->activate($activationToken);

        $this->assertTrue($adherent->isEnabled());
        $this->assertInstanceOf(\DateTime::class, $adherent->getActivatedAt());
        $this->assertInstanceOf(\DateTime::class, $activationToken->getUsageDate());
    }

    public function testActivateAdherentAccountTwice(): void
    {
        $this->expectException(AdherentAlreadyEnabledException::class);
        $adherent = $this->createNewAdherent();
        $activationToken = AdherentActivationToken::generate($adherent);

        $adherent->activate($activationToken);

        $adherent->activate($activationToken);
    }

    public function testAuthenticateAdherentAccount(): void
    {
        $adherent = $this->createNewAdherent();
        $this->assertNull($adherent->getLastLoggedAt());

        $adherent->recordLastLoginTime(new \DateTimeImmutable('2016-01-01 13:30:00'));
        $this->assertInstanceOf(\DateTime::class, $adherent->getLastLoggedAt());
        $this->assertEquals(new \DateTime('2016-01-01 13:30:00'), $adherent->getLastLoggedAt());
    }

    public function testUserWithLegislativeCandidateRole(): void
    {
        $adherent = $this->createNewAdherent();
        $this->assertNotContains('ROLE_LEGISLATIVE_CANDIDATE', $adherent->getRoles());

        $adherent = $this->createNewAdherent('john.smith@en-marche.fr');
        $this->assertNotContains('ROLE_LEGISLATIVE_CANDIDATE', $adherent->getRoles());
    }

    #[DataProvider('provideInitials')]
    public function testInitials(string $firstName, string $lastName, string $initials): void
    {
        $adherent = $this->createNewAdherent('john.smith@example.org', $firstName, $lastName);

        $this->assertSame($initials, $adherent->getInitials());
    }

    public static function provideInitials(): iterable
    {
        yield ['John', 'Smith', 'JS'];
        yield ['Jean-Pierre', 'Vandamme', 'JV'];
        yield ['Charles', 'Du Jardin', 'CD'];
        yield ['Jack', 'L\'éventreur', 'JL'];
    }

    private function createNewAdherent(
        $email = 'john.smith@example.org',
        $firstName = 'John',
        $lastName = 'Smith',
    ): Adherent {
        $phone = new PhoneNumber();
        $phone->setCountryCode('FR');
        $phone->setNationalNumber('0140998211');

        return Adherent::create(
            Adherent::createUuid($email),
            'ABC-234',
            $email,
            'super-password',
            'male',
            $firstName,
            $lastName,
            new \DateTime('1990-12-12'),
            ActivityPositionsEnum::STUDENT,
            $this->createPostAddress('92 bld du Général Leclerc', '92110-92024'),
            $phone
        );
    }
}
