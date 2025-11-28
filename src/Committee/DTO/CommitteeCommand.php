<?php

declare(strict_types=1);

namespace App\Committee\DTO;

use App\Address\Address;
use App\Entity\Committee;
use App\Validator\UniqueCommittee as AssertUniqueCommittee;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;

#[AssertUniqueCommittee]
class CommitteeCommand
{
    /** @var Committee */
    protected $committee;

    #[Assert\Sequentially([
        new Assert\NotBlank(),
        new Assert\Length(min: 2, max: 50),
    ])]
    public $name;

    #[Assert\Sequentially([
        new Assert\NotBlank(),
        new Assert\Length(min: 5, max: 140, minMessage: 'committee.description.min_length', maxMessage: 'committee.description.max_length'),
    ])]
    public $description;

    /**
     * The committee address.
     *
     * @var Address
     */
    #[Assert\Valid]
    protected $address;

    #[AssertPhoneNumber]
    #[Assert\Expression(expression: "(value == null and this.getCommittee()) or (value != '' and value != null)", message: 'common.phone_number.required', groups: ['created_by_adherent'])]
    protected $phone;

    #[Assert\Length(max: 255)]
    #[Assert\Url]
    public $facebookPageUrl;

    #[Assert\AtLeastOneOf([
        new Assert\Blank(),
        new Assert\Length(min: 1, max: 15),
    ])]
    #[Assert\Regex('/^@?([a-zA-Z0-9_]){1,15}$/', message: 'common.twitter_nickname.invalid_format')]
    public $twitterNickname;

    protected $nameLocked = false;

    protected $slug;

    protected function __construct(?Address $address = null)
    {
        $this->address = $address ?: new Address();
    }

    public static function createFromCommittee(Committee $committee): self
    {
        $dto = new self(Address::createFromAddress($committee->getPostAddress()));
        $dto->name = $committee->getName();
        $dto->description = $committee->getDescription();
        $dto->phone = $committee->getPhone();
        $dto->facebookPageUrl = $committee->getFacebookPageUrl();
        $dto->twitterNickname = $committee->getTwitterNickname();
        $dto->committee = $committee;
        $dto->nameLocked = $committee->isNameLocked();
        $dto->slug = $committee->getSlug();

        return $dto;
    }

    public function getCityName(): string
    {
        return $this->address->getCityName();
    }

    public function setPhone(?PhoneNumber $phone = null): void
    {
        $this->phone = $phone;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    public function getCommittee(): ?Committee
    {
        return $this->committee;
    }

    public function setAddress(Address $address): void
    {
        $this->address = $address;
    }

    public function getAddress(): Address
    {
        return $this->address;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function isNameLocked(): bool
    {
        return $this->nameLocked;
    }

    public function setNameLocked(bool $nameLocked): void
    {
        $this->nameLocked = $nameLocked;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }
}
