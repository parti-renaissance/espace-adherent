<?php

namespace AppBundle\Committee;

use AppBundle\Address\Address;
use AppBundle\Entity\Committee;
use AppBundle\Validator\UniqueCommittee as AssertUniqueCommittee;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AssertUniqueCommittee
 */
class CommitteeCommand
{
    /** @var Committee */
    protected $committee;

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
    protected $address;

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

    protected function __construct(Address $address = null)
    {
        $this->address = $address ?: new Address();
    }

    public static function createFromCommittee(Committee $committee): self
    {
        $dto = new self(Address::createFromAddress($committee->getPostAddress()), $committee);
        $dto->name = $committee->getName();
        $dto->description = $committee->getDescription();
        $dto->facebookPageUrl = $committee->getFacebookPageUrl();
        $dto->twitterNickname = $committee->getTwitterNickname();
        $dto->googlePlusPageUrl = $committee->getGooglePlusPageUrl();
        $dto->committee = $committee;

        return $dto;
    }

    public function updateCommittee(): Committee
    {
        if (!$this->committee) {
            throw new \RuntimeException('A Committee instance is required.');
        }

        $this->committee->update($this);
    }

    public function getCityName(): string
    {
        return $this->address->getCityName();
    }

    public function getCommittee(): ?Committee
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
