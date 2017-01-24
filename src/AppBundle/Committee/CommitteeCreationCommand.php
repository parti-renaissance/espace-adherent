<?php

namespace AppBundle\Committee;

use AppBundle\Address\Address;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Validator\UniqueCommittee as AssertUniqueCommittee;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
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
     * The committee address.
     *
     * @var Address
     *
     * @Assert\Valid
     */
    private $address;

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

    public function __construct(Adherent $adherent, Address $address = null)
    {
        $this->adherent = $adherent;
        $this->country = 'FR';
        $this->acceptConfidentialityTerms = false;
        $this->acceptContactingTerms = false;
        $this->address = $address ?: new Address();
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
        return $this->address->getCityName();
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

    public function setAddress(Address $address)
    {
        $this->address = $address;
    }

    public function getAddress(): Address
    {
        return $this->address;
    }
}
