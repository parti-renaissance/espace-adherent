<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Utils\AreaUtils;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CityRepository")
 * @ORM\Table(name="cities")
 *
 * @UniqueEntity("inseeCode", message="city.insee_code.unique")
 *
 * @Algolia\Index(autoIndex=false)
 */
class City
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(length=100)
     *
     * @Assert\NotBlank(message="city.name.not_blank")
     * @Assert\Length(max="100", maxMessage="city.name.max_length")
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(length=10, unique=true)
     *
     * @Assert\NotBlank(message="city.insee_code.not_blank")
     * @Assert\Length(max="10", maxMessage="city.insee_code.max_length")
     */
    private $inseeCode;

    /**
     * @var array|null
     *
     * @ORM\Column(type="simple_array")
     *
     * @Assert\NotBlank(message="city.postal_code.not_blank")
     * @Assert\Count(min="1")
     */
    private $postalCodes;

    /**
     * @var string
     *
     * @ORM\Column(length=2)
     *
     * @Assert\NotBlank(message="city.country.not_blank")
     * @Assert\Country(message="city.country.invalid")
     */
    private $country;

    public function __construct(
        string $name = null,
        string $inseeCode = null,
        array $postalCodes = null,
        string $country = AreaUtils::CODE_FRANCE
    ) {
        $this->name = $name;
        $this->inseeCode = $inseeCode ? self::normalizeCode($inseeCode) : null;
        $this->postalCodes = $postalCodes;
        $this->country = $country;
    }

    public function __toString()
    {
        return sprintf('%s (%s)', $this->name, $this->inseeCode);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getInseeCode(): ?string
    {
        return $this->inseeCode;
    }

    public function setInseeCode(?string $inseeCode): void
    {
        $this->inseeCode = self::normalizeCode($inseeCode);
    }

    public function getPostalCodes(): ?array
    {
        return $this->postalCodes;
    }

    public function setPostalCodes(?array $postalCodes): void
    {
        $this->postalCodes = $postalCodes;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    public static function normalizeCode(string $inseeCode): string
    {
        return str_pad($inseeCode, 5, '0', \STR_PAD_LEFT);
    }

    public function equals(self $city): bool
    {
        return $this->id === $city->getId();
    }
}
