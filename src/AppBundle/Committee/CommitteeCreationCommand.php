<?php

namespace AppBundle\Committee;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Intl\FranceCitiesBundle;
use AppBundle\Validator\CityAssociatedToPostalCode as AssertCityAssociatedToPostalCode;
use AppBundle\Validator\FrenchCity as AssertFrenchCity;
use AppBundle\Validator\FrenchPostalCode as AssertFrenchPostalCode;
use AppBundle\Validator\UniqueCommittee as AssertUniqueCommittee;
use AppBundle\Validator\UnitedNationsCountry as AssertUnitedNationsCountry;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AssertCityAssociatedToPostalCode(postalCodeField="postalCode", cityField="city", message="common.city.invalid_postal_code")
 * @AssertUniqueCommittee
 */
class CommitteeCreationCommand
{
    /** @var Adherent */
    private $adherent;

    /** @var Committee */
    private $committee;

    /**
     * @Assert\NotBlank
     * @Assert\Length(min=2, max=50)
     */
    public $name;

    /**
     * @Assert\NotBlank
     * @Assert\Length(min=5, max=140)
     */
    public $description;

    /**
     * @AssertFrenchPostalCode(message="common.postal_code.invalid")
     */
    public $postalCode;

    /**
     * @AssertFrenchCity(message="common.city.invalid")
     */
    public $city;

    /**
     * @AssertUnitedNationsCountry(message="common.country.invalid")
     */
    public $country;

    /**
     * @Assert\Url
     */
    public $facebookPageUrl;

    /**
     * @Assert\Length(min=1, max=15)
     * @Assert\Regex("/^@?([a-zA-Z0-9_]){1,15}$/", message="common.twitter_nickname.invalid_format")
     */
    public $twitterNickname;

    /**
     * @Assert\Url
     */
    public $googlePlusPageUrl;

    /**
     * @Assert\IsTrue(message="committee.must_accept_confidentiality_terms")
     */
    public $acceptConfidentialityTerms;

    /**
     * @Assert\IsTrue(message="committee.must_accept_contacting_terms")
     */
    public $acceptContactingTerms;

    public function __construct(Adherent $adherent)
    {
        $this->adherent = $adherent;
        $this->country = 'FR';
        $this->acceptConfidentialityTerms = false;
        $this->acceptContactingTerms = false;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }

    public function setCommittee(Committee $committee)
    {
        $this->committee = $committee;
    }

    public function getCityName(): string
    {
        $name = FranceCitiesBundle::getCity($this->postalCode, $this->city);

        return $name ?: '';
    }

    public function getCommittee(): Committee
    {
        return $this->committee;
    }

    public function getCommitteeUuid(): UuidInterface
    {
        return $this->committee->getUuid();
    }

    public function getCommitteeSlug(): string
    {
        return $this->committee->getSlug();
    }
}
