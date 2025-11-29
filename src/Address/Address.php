<?php

declare(strict_types=1);

namespace App\Address;

use App\Geocoder\GeocodableInterface;
use App\Validator\Address as AssertValidAddress;
use App\Validator\FrenchAddress;
use App\Validator\GeocodableAddress as AssertGeocodableAddress;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[AssertGeocodableAddress(groups: ['Default'])]
#[AssertGeocodableAddress(message: 'admin.common.address.not_geocodable', groups: ['admin_adherent_renaissance_create'])]
#[AssertValidAddress(groups: ['Default'])]
#[FrenchAddress(groups: ['fill_personal_info'])]
class Address implements AddressInterface, GeocodableInterface
{
    use AddressTrait;

    #[Assert\Expression(expression: "value or 'FR' != this.getCountry()", message: 'common.address.required', groups: ['fill_personal_info'])]
    #[Assert\Length(max: 150, maxMessage: 'common.address.max_length', groups: ['Default', 'Update', 'fill_personal_info'])]
    #[Assert\NotBlank(message: 'common.address.required', groups: ['Default', 'Update'])]
    #[Groups(['profile_write', 'membership:write'])]
    protected ?string $address = null;

    #[Assert\Length(max: 150, maxMessage: 'common.address.max_length', groups: ['Default', 'Update', 'fill_personal_info'])]
    #[Groups(['profile_write', 'membership:write'])]
    protected ?string $additionalAddress = null;

    #[Assert\Expression(expression: "value or 'FR' != this.getCountry()", message: 'common.postal_code.not_blank', groups: ['Default', 'Registration', 'Update', 'fill_personal_info'])]
    #[Assert\Length(max: 15, maxMessage: 'common.postal_code.max_length', groups: ['Default', 'Registration', 'Update', 'fill_personal_info'])]
    #[Groups(['profile_write', 'membership:write'])]
    protected ?string $postalCode = null;

    #[Assert\Length(max: 15, groups: ['Default', 'Update', 'fill_personal_info'])]
    #[Groups(['profile_write', 'membership:write'])]
    protected ?string $city = null;

    #[Assert\Expression(expression: "value or ('FR' === this.getCountry() and this.getCity())", message: 'common.city_name.not_blank', groups: ['Update', 'fill_personal_info'])]
    #[Assert\Length(max: 255, groups: ['Default', 'Update', 'fill_personal_info'])]
    #[Groups(['profile_write', 'membership:write'])]
    protected ?string $cityName = null;

    #[Assert\Country(message: 'common.country.invalid', groups: ['Default', 'Registration', 'Update', 'fill_personal_info'])]
    #[Assert\NotBlank(message: 'common.country.not_blank', groups: ['Default', 'Registration', 'Update', 'fill_personal_info'])]
    #[Groups(['profile_write', 'membership:write'])]
    protected ?string $country = null;
}
