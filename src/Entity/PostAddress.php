<?php

namespace App\Entity;

use App\Address\AddressInterface;
use App\Geocoder\GeocodableInterface;
use App\Geocoder\GeoPointInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Embeddable]
class PostAddress implements AddressInterface, GeocodableInterface, GeoPointInterface
{
    use EntityAddressTrait;

    /**
     * The address street.
     */
    #[Assert\Length(max: 150, groups: ['contact_update', 'procuration:write'])]
    #[Groups(['profile_read', 'contact_read_after_write', 'contact_update', 'procuration_request_read', 'procuration_request_list', 'procuration_proxy_list', 'procuration_matched_proxy', 'action_read', 'action_read_list', 'action_write', 'profile_update'])]
    #[ORM\Column(length: 150, nullable: true)]
    protected ?string $address = null;

    /**
     * The address zip code.
     */
    #[Assert\Length(max: 15, groups: ['contact_update', 'procuration:write'])]
    #[Groups(['profile_read', 'contact_read_after_write', 'contact_update', 'procuration_request_read', 'procuration_request_list', 'procuration_proxy_list', 'procuration_matched_proxy', 'action_read', 'action_read_list', 'action_write', 'profile_update'])]
    #[ORM\Column(length: 15, nullable: true)]
    protected ?string $postalCode = null;

    /**
     * The address city code (postal code + INSEE code).
     */
    #[Assert\Length(max: 15, groups: ['contact_update', 'procuration:write'])]
    #[Groups(['contact_read_after_write', 'contact_update', 'profile_read', 'procuration_request_read', 'procuration_request_list', 'procuration_proxy_list', 'procuration_matched_proxy', 'action_read', 'action_read_list', 'action_write', 'profile_update'])]
    #[ORM\Column(name: 'city_insee', length: 15, nullable: true)]
    protected ?string $city = null;

    /**
     * The address city name.
     */
    #[Assert\Length(max: 255, groups: ['contact_update', 'procuration:write'])]
    #[Groups(['profile_read', 'contact_read_after_write', 'contact_update', 'procuration_request_read', 'procuration_request_list', 'procuration_proxy_list', 'procuration_matched_proxy', 'action_read', 'action_read_list', 'action_write', 'profile_update'])]
    #[ORM\Column(nullable: true)]
    protected ?string $cityName = null;

    /**
     * The address country code (ISO2).
     */
    #[Assert\Country(groups: ['contact_update', 'procuration:write'])]
    #[Groups(['profile_read', 'contact_read_after_write', 'contact_update', 'procuration_request_read', 'procuration_request_list', 'procuration_proxy_list', 'procuration_matched_proxy', 'action_read', 'action_read_list', 'action_write', 'profile_update'])]
    #[ORM\Column(length: 2, nullable: true)]
    protected ?string $country = null;

    public static function createEmptyAddress(): self
    {
        return new self(AddressInterface::FRANCE);
    }

    public static function createCountryAddress(string $country): self
    {
        return new self($country);
    }

    public static function createFrenchAddress(
        ?string $street = null,
        ?string $cityCode = null,
        ?string $cityName = null,
        ?string $additionalAddress = null,
        ?string $region = null,
        ?float $latitude = null,
        ?float $longitude = null,
    ): self {
        [$postalCode, $inseeCode] = explode('-', $cityCode);

        $address = new self(
            AddressInterface::FRANCE,
            $postalCode,
            $cityName,
            $street,
            $additionalAddress,
            $latitude,
            $longitude,
            $region
        );

        $address->city = \sprintf('%s-%s', $postalCode, $inseeCode);

        return $address;
    }

    public static function createForeignAddress(
        string $country,
        ?string $zipCode,
        ?string $cityName,
        ?string $street,
        ?string $additionalAddress = null,
        ?string $region = null,
        ?float $latitude = null,
        ?float $longitude = null,
    ): self {
        return new self($country, $zipCode, $cityName, $street, $additionalAddress, $latitude, $longitude, $region);
    }
}
