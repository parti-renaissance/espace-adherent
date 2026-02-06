<?php

declare(strict_types=1);

namespace Tests\App\Validator;

use App\Address\AddressInterface;
use App\Donation\Request\DonationRequest;
use App\Validator\FrenchAddressOrNationalityDonation;
use App\Validator\FrenchAddressOrNationalityDonationValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class FrenchAddressOrNationalityDonationValidatorTest extends ConstraintValidatorTestCase
{
    #[DataProvider('noValidateDonationProvider')]
    public function testNoViolation(string $nationality, string $country): void
    {
        $donationRequest = $this->createDonationRequest($nationality, $country);

        $this->setObject($donationRequest);

        $this->validator->validate($donationRequest, new FrenchAddressOrNationalityDonation());

        $this->assertNoViolation();
    }

    public static function noValidateDonationProvider(): iterable
    {
        yield 'No violation if french nationality' => [
            'FR',
            'DE',
        ];
        yield 'No violation if french country' => [
            'DE',
            'FR',
        ];
        yield 'No violation if french nationality and french country' => [
            'FR',
            'FR',
        ];
    }

    #[DataProvider('violationProvider')]
    public function testViolation(?string $nationality, ?string $country): void
    {
        $donationRequest = $this->createDonationRequest($nationality, $country);

        $this->setObject($donationRequest);

        $this->validator->validate($donationRequest, new FrenchAddressOrNationalityDonation());

        $this
            ->buildViolation('donation.french_address_or_nationality_donation')
            ->assertRaised()
        ;
    }

    public static function violationProvider(): iterable
    {
        yield ['DE', 'DE'];
        yield ['DE', 'GB'];
        yield ['GB', 'DE'];
        yield ['GB', 'GB'];
        yield [null, 'IT'];
        yield ['IT', null];
        yield [null, null];
    }

    protected function createValidator(): FrenchAddressOrNationalityDonationValidator
    {
        return new FrenchAddressOrNationalityDonationValidator();
    }

    private function createDonationRequest(
        ?string $nationality = AddressInterface::FRANCE,
        ?string $country = AddressInterface::FRANCE,
    ): DonationRequest {
        $donationRequest = new DonationRequest(DonationRequest::DEFAULT_AMOUNT, clientIp: '123.0.0.1');

        $donationRequest->setNationality($nationality);
        $donationRequest->getAddress()->setCountry($country);

        return $donationRequest;
    }
}
