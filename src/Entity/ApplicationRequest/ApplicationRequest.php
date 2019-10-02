<?php

namespace AppBundle\Entity\ApplicationRequest;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\EntityIdentityTrait;
use AppBundle\Entity\EntityReferentTagTrait;
use AppBundle\Entity\EntityTimestampableTrait;
use AppBundle\Entity\ReferentTaggableEntity;
use AppBundle\Intl\FranceCitiesBundle;
use AppBundle\Validator\UnitedNationsCountry as AssertUnitedNationsCountry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 */
abstract class ApplicationRequest implements ReferentTaggableEntity
{
    use EntityIdentityTrait;
    use EntityReferentTagTrait;
    use EntityTimestampableTrait;

    /**
     * @ORM\Column(length=6, nullable=true)
     *
     * @Assert\NotBlank(message="common.gender.not_blank")
     * @Assert\Choice(choices=AppBundle\ValueObject\Genders::CHOICES, strict=true)
     */
    private $gender;

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @Assert\NotBlank(message="application_request.first_name.not_blank")
     * @Assert\Length(
     *     min=2,
     *     max=100,
     *     minMessage="application_request.first_name.min_length",
     *     maxMessage="application_request.first_name.max_length"
     * )
     */
    protected $firstName;

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @Assert\NotBlank(message="application_request.last_name.not_blank")
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     minMessage="application_request.last_name.min_length",
     *     maxMessage="application_request.last_name.max_length"
     * )
     */
    protected $lastName;

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array")
     *
     * @Assert\Count(
     *     min=1,
     *     max=2,
     *     minMessage="application_request.favorite_cities.min_length",
     *     maxMessage="application_request.favorite_cities.max_length"
     * )
     */
    protected $favoriteCities = [];

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @Assert\NotBlank(message="application_request.email_address.not_blank")
     * @Assert\Email(message="common.email.invalid")
     * @Assert\Length(max=255, maxMessage="common.email.max_length")
     */
    protected $emailAddress;

    /**
     * @var string|null
     *
     * @ORM\Column(length=150)
     *
     * @Assert\NotBlank(message="common.address.required")
     * @Assert\Length(max=150, maxMessage="common.address.max_length")
     */
    protected $address;

    /**
     * @var string|null
     *
     * @ORM\Column(length=15, nullable=true)
     *
     * @Assert\NotBlank(message="common.postal_code.not_blank")
     * @Assert\Length(max=15)
     */
    protected $postalCode;

    /**
     * The address city code (postal code + INSEE code).
     *
     * @var string|null
     *
     * @ORM\Column(length=20, nullable=true)
     *
     * @Assert\Length(max=20)
     */
    protected $city;

    /**
     * @var string|null
     *
     * @ORM\Column(length=50)
     *
     * @Assert\NotBlank(message="common.city_name.not_blank")
     * @Assert\Length(max=50)
     * @Assert\Expression(expression="(this.getCountry() === 'FR' and this.getCity()) or value", message="common.city_name.not_blank")
     */
    protected $cityName;

    /**
     * @var string
     *
     * @ORM\Column(length=2)
     *
     * @Assert\NotBlank
     * @AssertUnitedNationsCountry(message="common.country.invalid")
     */
    protected $country = 'FR';

    /**
     * @var PhoneNumber|null
     *
     * @ORM\Column(type="phone_number", nullable=true)
     *
     * @Assert\NotBlank(message="common.phone_number.required")
     * @AssertPhoneNumber(defaultRegion="FR")
     */
    protected $phone;

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @Assert\NotBlank(message="application_request.profession.required")
     * @Assert\Length(max=255, maxMessage="application_request.profession.max_length")
     */
    protected $profession;

    /**
     * @var Theme[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\ApplicationRequest\Theme")
     *
     * @Assert\Count(min=1, minMessage="application_request.favorite_themes.min")
     */
    protected $favoriteThemes;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $customFavoriteTheme;

    /**
     * @var Adherent|null
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent")
     */
    private $adherent;

    /**
     * @var ApplicationRequestTag[]|Collection
     *
     * @Assert\Valid(groups={"ApplicationRequestTag"})
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\ApplicationRequest\ApplicationRequestTag")
     */
    protected $tags;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $takenForCity;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private $displayed = true;

    public function __construct(UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?: Uuid::uuid4();
        $this->favoriteThemes = new ArrayCollection();
        $this->referentTags = new ArrayCollection();
        $this->tags = new ArrayCollection();

        $this->phone = new PhoneNumber();
        $this->phone->setCountryCode(33);
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getFullName(): string
    {
        return $this->firstName.' '.$this->lastName;
    }

    public function getFavoriteCities(): array
    {
        return $this->favoriteCities;
    }

    public function getFavoriteCityPrefixedCodes(): array
    {
        return array_map(static function (string $code) {return '#'.$code; }, $this->getFavoriteCities());
    }

    public function getFavoriteCitiesNames(): array
    {
        return FranceCitiesBundle::searchCitiesByInseeCodes($this->getFavoriteCities());
    }

    public function setFavoriteCities(array $favoriteCities): void
    {
        $this->favoriteCities = $favoriteCities;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(?string $emailAddress): void
    {
        $this->emailAddress = $emailAddress;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getCityName(): ?string
    {
        return $this->cityName;
    }

    public function setCityName(?string $cityName): void
    {
        $this->cityName = $cityName;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    public function setPhone(?PhoneNumber $phone): void
    {
        $this->phone = $phone;
    }

    public function getProfession(): ?string
    {
        return $this->profession;
    }

    public function setProfession(?string $profession): void
    {
        $this->profession = $profession;
    }

    public function getFavoriteThemes(): Collection
    {
        return $this->favoriteThemes;
    }

    public function addFavoriteTheme(Theme $favoriteTheme): void
    {
        if (!$this->favoriteThemes->contains($favoriteTheme)) {
            $this->favoriteThemes->add($favoriteTheme);
        }
    }

    public function getCustomFavoriteTheme(): ?string
    {
        return $this->customFavoriteTheme;
    }

    public function setCustomFavoriteTheme(?string $customFavoriteTheme): void
    {
        $this->customFavoriteTheme = $customFavoriteTheme;
    }

    public function isAdherent(): bool
    {
        return null !== $this->adherent;
    }

    public function setAdherent(?Adherent $adherent): void
    {
        $this->adherent = $adherent;
    }

    public function __toString(): string
    {
        return $this->firstName.' '.$this->lastName;
    }

    public function getTags(): array
    {
        return $this->tags->toArray();
    }

    public function addTag(ApplicationRequestTag $tag): void
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }
    }

    public function removeTag(ApplicationRequestTag $tag): void
    {
        $this->tags->removeElement($tag);
    }

    public function getFavoriteCitiesAsString(): string
    {
        $cities = [];
        foreach ($this->favoriteCities as $inseeCode) {
            $city = FranceCitiesBundle::getCityDataFromInseeCode($inseeCode);

            $cities[] = !empty($city['name'])
                ? sprintf('%s (%s)', $city['name'], $city['postal_code'])
                : $inseeCode;
        }

        return implode(', ', $cities);
    }

    public function getFavoriteThemesAsString(): string
    {
        $themes = array_map(function (Theme $theme) {
            return $theme->getName();
        }, $this->favoriteThemes->toArray());

        if (!empty($this->customFavoriteTheme)) {
            $themes[] = $this->customFavoriteTheme;
        }

        return implode(', ', $themes);
    }

    public function getTakenForCity(): ?string
    {
        return $this->takenForCity;
    }

    public function setTakenForCity(?string $takenForCity): void
    {
        $this->takenForCity = $takenForCity;
    }

    public function isDisplayed(): bool
    {
        return $this->displayed;
    }

    public function setDisplayed(bool $displayed): void
    {
        $this->displayed = $displayed;
    }

    abstract public function getType(): string;
}
