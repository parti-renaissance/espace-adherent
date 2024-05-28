<?php

namespace App\Entity;

use App\Address\AddressInterface;
use App\Geocoder\GeocodableInterface;
use App\Geocoder\GeoPointInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Embeddable
 */
class PostAddress implements AddressInterface, GeocodableInterface, GeoPointInterface
{
    use EntityAddressTrait;

    /**
     * The address street.
     *
     * @ORM\Column(length=150, nullable=true)
     *
     * @Assert\Length(max=150, groups={"contact_update", "procuration:write"})
     */
    #[Groups(['profile_read', 'contact_read_after_write', 'contact_update', 'procuration_request_read', 'procuration_request_list', 'procuration_proxy_list', 'procuration_matched_proxy', 'action_read', 'action_read_list', 'action_write'])]
    protected ?string $address = null;

    /**
     * The address zip code.
     *
     * @ORM\Column(length=15, nullable=true)
     *
     * @Assert\Length(max=15, groups={"contact_update", "procuration:write"})
     */
    #[Groups(['profile_read', 'contact_read_after_write', 'contact_update', 'procuration_request_read', 'procuration_request_list', 'procuration_proxy_list', 'procuration_matched_proxy', 'action_read', 'action_read_list', 'action_write'])]
    protected ?string $postalCode = null;

    /**
     * The address city code (postal code + INSEE code).
     *
     * @ORM\Column(length=15, nullable=true, name="city_insee")
     *
     * @Assert\Length(max=15, groups={"contact_update", "procuration:write"})
     */
    #[Groups(['contact_read_after_write', 'contact_update', 'profile_read', 'procuration_request_read', 'procuration_request_list', 'procuration_proxy_list', 'procuration_matched_proxy', 'action_read', 'action_read_list', 'action_write'])]
    protected ?string $city = null;

    /**
     * The address city name.
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255, groups={"contact_update", "procuration:write"})
     */
    #[Groups(['profile_read', 'contact_read_after_write', 'contact_update', 'procuration_request_read', 'procuration_request_list', 'procuration_proxy_list', 'procuration_matched_proxy', 'action_read', 'action_read_list', 'action_write'])]
    protected ?string $cityName = null;

    /**
     * The address country code (ISO2).
     *
     * @ORM\Column(length=2, nullable=true)
     *
     * @Assert\Country(groups={"contact_update", "procuration:write"})
     */
    #[Groups(['profile_read', 'contact_read_after_write', 'contact_update', 'procuration_request_read', 'procuration_request_list', 'procuration_proxy_list', 'procuration_matched_proxy', 'action_read', 'action_read_list', 'action_write'])]
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
        ?float $longitude = null
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

        $address->city = sprintf('%s-%s', $postalCode, $inseeCode);

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
        ?float $longitude = null
    ): self {
        return new self($country, $zipCode, $cityName, $street, $additionalAddress, $latitude, $longitude, $region);
    }
}
