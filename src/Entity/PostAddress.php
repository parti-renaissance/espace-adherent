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
     */
    #[Assert\Length(max: 150, groups: ['contact_update'])]
    #[Groups(['profile_read', 'contact_read_after_write', 'contact_update'])]
    protected ?string $address = null;

    /**
     * The address zip code.
     *
     * @ORM\Column(length=15, nullable=true)
     */
    #[Assert\Length(max: 15, groups: ['contact_update'])]
    #[Groups(['profile_read', 'contact_read_after_write', 'contact_update'])]
    protected ?string $postalCode = null;

    /**
     * The address city code (postal code + INSEE code).
     *
     * @ORM\Column(length=15, nullable=true, name="city_insee")
     */
    #[Assert\Length(max: 15, groups: ['contact_update'])]
    #[Groups(['contact_read_after_write', 'contact_update', 'profile_read'])]
    protected ?string $city = null;

    /**
     * The address city name.
     *
     * @ORM\Column(nullable=true)
     */
    #[Assert\Length(max: 255, groups: ['contact_update'])]
    #[Groups(['profile_read', 'contact_read_after_write', 'contact_update'])]
    protected ?string $cityName = null;

    /**
     * The address country code (ISO2).
     *
     * @ORM\Column(length=2, nullable=true)
     */
    #[Assert\Country(groups: ['contact_update'])]
    #[Groups(['profile_read', 'contact_read_after_write', 'contact_update'])]
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
        string $street = null,
        string $cityCode = null,
        string $cityName = null,
        string $region = null,
        float $latitude = null,
        float $longitude = null
    ): self {
        [$postalCode, $inseeCode] = explode('-', $cityCode);

        $address = new self(
            AddressInterface::FRANCE,
            $postalCode,
            $cityName,
            $street,
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
        string $region = null,
        float $latitude = null,
        float $longitude = null
    ): self {
        return new self($country, $zipCode, $cityName, $street, $latitude, $longitude, $region);
    }
}
