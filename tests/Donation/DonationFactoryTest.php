<?php

declare(strict_types=1);

namespace Tests\App\Donation;

use App\Address\PostAddressFactory;
use App\Donation\DonationFactory;
use App\Donation\Request\DonationRequest;
use App\Donation\Request\DonationRequestUtils;
use App\Entity\Donator;
use App\Repository\Geo\ZoneRepository;
use Cocur\Slugify\Slugify;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class DonationFactoryTest extends TestCase
{
    public function testCreateDonationFromDonationRequest(): void
    {
        $phone = new PhoneNumber();
        $phone->setCountryCode('FR');
        $phone->setNationalNumber('0123456789');

        $request = new DonationRequest('3.3.3.3');
        $request->firstName = 'Damien';
        $request->lastName = 'DUPONT';
        $request->gender = 'male';
        $request->setAmount(70.0);
        $request->setEmailAddress('m.dupont@example.fr');
        $request->getAddress()->setCountry('FR');
        $request->getAddress()->setPostalCode('69000');
        $request->getAddress()->setCityName('Lyon');
        $request->getAddress()->setAddress('2, Rue de la République');
        $request->setDuration(0);

        $donator = $this->createConfiguredMock(Donator::class, [
            'getGender' => 'male',
            'getFirstName' => 'Damien',
            'getLastName' => 'DUPONT',
            'getEmailAddress' => 'm.dupont@example.fr',
        ]);

        $factory = $this->createFactory();
        $donation = $factory->createFromDonationRequest($request, $donator);

        $this->assertSame('m.dupont@example.fr', $donation->getDonator()->getEmailAddress());
        $this->assertSame('male', $donation->getDonator()->getGender());
        $this->assertSame('Damien', $donation->getDonator()->getFirstName());
        $this->assertSame('DUPONT', $donation->getDonator()->getLastName());
        $this->assertSame('FR', $donation->getCountry());
        $this->assertSame('2, Rue de la République', $donation->getAddress());
        $this->assertSame('Lyon', $donation->getCityName());
        $this->assertSame('69000', $donation->getPostalCode());
        $this->assertSame(7000, $donation->getAmount());
        $this->assertSame(70.0, $donation->getAmountInEuros());
        $this->assertSame(0, $donation->getDuration());
        $this->assertSame('3.3.3.3', $donation->getClientIp());
    }

    private function createFactory(): DonationFactory
    {
        return new DonationFactory(
            new PostAddressFactory(),
            new DonationRequestUtils(
                $this->createMock(CsrfTokenManagerInterface::class),
                Slugify::create(),
            ),
            $this->createMock(ZoneRepository::class)
        );
    }
}
