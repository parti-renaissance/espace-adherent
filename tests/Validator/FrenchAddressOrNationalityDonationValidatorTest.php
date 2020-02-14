<?php

namespace Tests\AppBundle\Validator;

use AppBundle\Donation\DonationRequest;
use AppBundle\Utils\AreaUtils;
use AppBundle\Validator\FrenchAddressOrNationalityDonation;
use AppBundle\Validator\FrenchAddressOrNationalityDonationValidator;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class FrenchAddressOrNationalityDonationValidatorTest extends ConstraintValidatorTestCase
{
    /**
     * @dataProvider noValidateDonationProvider
     */
    public function testNoViolation(string $nationality, string $country): void
    {
        $donationRequest = $this->createDonationRequest($nationality, $country);

        $this->setObject($donationRequest);

        $this->validator->validate($donationRequest, new FrenchAddressOrNationalityDonation());

        $this->assertNoViolation();
    }

    public function noValidateDonationProvider(): iterable
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

    /**
     * @dataProvider violationProvider
     */
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

    public function violationProvider(): iterable
    {
        yield ['DE', 'DE'];
        yield ['DE', 'GB'];
        yield ['GB', 'DE'];
        yield ['GB', 'GB'];
        yield [null, 'IT'];
        yield ['IT', null];
        yield [null, null];
    }

    protected function createValidator()
    {
        return new FrenchAddressOrNationalityDonationValidator();
    }

    private function createDonationRequest(
        ?string $nationality = AreaUtils::CODE_FRANCE,
        ?string $country = AreaUtils::CODE_FRANCE
    ): DonationRequest {
        $donationRequest = new DonationRequest(Uuid::uuid4(), '123.0.0.1');

        $donationRequest->setNationality($nationality);
        $donationRequest->setCountry($country);

        return $donationRequest;
    }
}
