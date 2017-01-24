<?php

namespace Tests\AppBundle\Validator;

use AppBundle\Address\Address;
use AppBundle\Entity\PostAddress;
use AppBundle\Geocoder\DummyGeocoder;
use AppBundle\Geocoder\GeocodableInterface;
use AppBundle\Validator\GeocodableAddress;
use AppBundle\Validator\GeocodableAddressValidator;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class GeocodableAddressValidatorTest extends ConstraintValidatorTestCase
{
    /**
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testUnsupportedValue()
    {
        $this->validator->validate(12345, new GeocodableAddress());
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testUnsupportedConstraint()
    {
        $this->validator->validate(
            PostAddress::createFrenchAddress('92 bld Victor Hugo', '92110-92024'),
            new NotBlank()
        );
    }

    public function testSkipValidation()
    {
        $this->validator->validate(null, new GeocodableAddress());
        $this->assertNoViolation();

        $this->validator->validate('', new GeocodableAddress());
        $this->assertNoViolation();
    }

    /**
     * @dataProvider provideGeocodableAddress
     */
    public function testAddressIsValid(GeocodableInterface $address)
    {
        $this->validator->validate($address, new GeocodableAddress());
        $this->assertNoViolation();
    }

    public function provideGeocodableAddress()
    {
        $address = new Address();
        $address->setCountry('CH');
        $address->setAddress('36 Zeppelinstrasse');
        $address->setPostalCode('8057');
        $address->setCityName('Zürich');

        return [
            [PostAddress::createFrenchAddress('92 bld Victor Hugo', '92110-92024')],
            [PostAddress::createForeignAddress('CH', '8057', 'Zürich', '36 Zeppelinstrasse')],
            [$address],
        ];
    }

    public function testAddressIsNotValid()
    {
        $address = new Address();
        $address->setCountry('FR');
        $address->setAddress('80 rue des lapinoux');
        $address->setPostalCode('75015');
        $address->setCityName('Paris 15e Arrondissement');

        $this->validator->validate($address, new GeocodableAddress());

        $this
            ->buildViolation('common.address.not_geocodable')
            ->setCode(GeocodableAddress::INVALID_ERROR)
            ->assertRaised();
    }

    protected function createValidator()
    {
        return new GeocodableAddressValidator(new DummyGeocoder());
    }
}
