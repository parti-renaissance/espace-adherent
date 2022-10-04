<?php

namespace Tests\App\Donation;

use App\Donation\DonationRequest;
use App\Entity\Adherent;
use libphonenumber\PhoneNumber;
use Tests\App\AbstractKernelTestCase;

class DonationRequestTest extends AbstractKernelTestCase
{
    public function testCreateDonationRequestFromAdherent()
    {
        $email = 'm.dupont@example.com';
        $uuid = Adherent::createUuid($email);
        $phone = new PhoneNumber();
        $phone->setCountryCode('FR');
        $phone->setNationalNumber('0123456789');

        $adherent = Adherent::create(
            $uuid,
            $email,
            'password',
            'male',
            'Damien',
            'DUPONT',
            new \DateTime('1979-03-25'),
            'position',
            $this->createPostAddress('2 Rue de la République', '69001-69381'),
            $phone
        );

        $donationRequest = DonationRequest::createFromAdherent($adherent, '3.3.3.3', '30.0', 0);

        $this->assertInstanceOf(DonationRequest::class, $donationRequest);
        $this->assertSame('male', $donationRequest->getGender());
        $this->assertSame('Damien', $donationRequest->getFirstName());
        $this->assertSame('DUPONT', $donationRequest->getLastName());
        $this->assertSame($email, $donationRequest->getEmailAddress());
        $this->assertSame('2 Rue de la République', $donationRequest->getAddress()->getAddress());
        $this->assertSame('69001', $donationRequest->getAddress()->getPostalCode());
        $this->assertSame('69001-69381', $donationRequest->getAddress()->getCity());
        $this->assertSame('Lyon 1er', $donationRequest->getAddress()->getCityName());
        $this->assertSame('FR', $donationRequest->getAddress()->getCountry());
        $this->assertSame(30.0, $donationRequest->getAmount());
        $this->assertSame(0, $donationRequest->getDuration());
        $this->assertSame('3.3.3.3', $donationRequest->getClientIp());
    }
}
