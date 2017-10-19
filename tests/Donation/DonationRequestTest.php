<?php

namespace Tests\AppBundle\Donation;

use AppBundle\Donation\DonationRequest;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\PostAddress;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

class DonationRequestTest extends TestCase
{
    public function testCreateDonationRequestFromAdherent()
    {
        $email = 'm.dupont@example.com';
        $uuid = Adherent::createUuid($email);
        $phone = new PhoneNumber();
        $phone->setCountryCode('FR');
        $phone->setNationalNumber('0123456789');

        $adherent = new Adherent(
            $uuid,
            $email,
            'male',
            'Damien',
            'DUPONT',
            new \DateTime('1979-03-25'),
            'position',
            PostAddress::createFrenchAddress('2 Rue de la République', '69001-69381'),
            $phone
        );

        $donationRequest = DonationRequest::createFromAdherent($adherent, '3.3.3.3', '30.0', 0);

        $this->assertInstanceOf(DonationRequest::class, $donationRequest);
        $this->assertInstanceOf(UuidInterface::class, $donationRequest->getUuid());
        $this->assertSame('male', $donationRequest->getGender());
        $this->assertSame('Damien', $donationRequest->getFirstName());
        $this->assertSame('DUPONT', $donationRequest->getLastName());
        $this->assertSame($email, $donationRequest->getEmailAddress());
        $this->assertSame('2 Rue de la République', $donationRequest->getAddress());
        $this->assertSame('69001', $donationRequest->getPostalCode());
        $this->assertSame('69001-69381', $donationRequest->getCity());
        $this->assertSame('Lyon 1er', $donationRequest->getCityName());
        $this->assertSame('FR', $donationRequest->getCountry());
        $this->assertEquals($phone, $donationRequest->getPhone());
        $this->assertSame(30.0, $donationRequest->getAmount());
        $this->assertSame(0, $donationRequest->getDuration());
        $this->assertSame('3.3.3.3', $donationRequest->getClientIp());
    }
}
