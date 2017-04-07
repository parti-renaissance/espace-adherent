<?php

namespace AppBundle\Referent;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CommitteeMembership;
use AppBundle\Entity\NewsletterSubscription;
use libphonenumber\PhoneNumber;

class ManagedUser
{
    const TYPE_ADHERENT = 'adherent';
    const TYPE_NEWSLETTER_SUBSCRIBER = 'newsletter_subscriber';

    /**
     * @var string
     */
    private $type;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $email;

    /**
     * @var bool
     */
    private $emailVisible;

    /**
     * @var string
     */
    private $postalCode;

    /**
     * @var string|null
     */
    private $firstName;

    /**
     * @var string|null
     */
    private $lastName;

    /**
     * @var \DateTime|null
     */
    private $birthdate;

    /**
     * @var string|null
     */
    private $city;

    /**
     * @var string|null
     */
    private $country;

    /**
     * @var PhoneNumber|null
     */
    private $phone;

    /**
     * @var array
     */
    private $committees;

    /**
     * @var \DateTime|null
     */
    private $createdAt;

    /**
     * @var bool
     */
    private $isHost;

    /**
     * @var bool
     */
    private $referentsEmailsSubscription;

    public function __construct(
        string $type,
        int $id,
        string $email,
        \DateTime $createdAt = null,
        string $postalCode = null,
        bool $emailVisible = false,
        string $firstName = null,
        string $lastName = null,
        \DateTime $birthdate = null,
        string $city = null,
        string $country = null,
        PhoneNumber $phone = null,
        bool $isHost = false,
        array $committeesUuid = [],
        bool $referentsEmailsSubscription = true
    ) {
        if (!in_array($type, [self::TYPE_ADHERENT, self::TYPE_NEWSLETTER_SUBSCRIBER], true)) {
            throw new \InvalidArgumentException('Invalid ManagedUser type');
        }

        $this->type = $type;
        $this->id = $id;
        $this->email = $email;
        $this->emailVisible = $emailVisible;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->birthdate = $birthdate;
        $this->createdAt = $createdAt;
        $this->postalCode = $postalCode;
        $this->city = $city;
        $this->country = $country;
        $this->phone = $phone;
        $this->isHost = $isHost;
        $this->committees = $committeesUuid;
        $this->referentsEmailsSubscription = $referentsEmailsSubscription;
    }

    public static function createFromAdherent(Adherent $adherent): ManagedUser
    {
        return new self(
            self::TYPE_ADHERENT,
            $adherent->getId(),
            $adherent->getEmailAddress(),
            $adherent->getRegisteredAt(),
            $adherent->getPostalCode(),
            $adherent->isHost(),
            $adherent->getFirstName(),
            $adherent->getLastName(),
            $adherent->getBirthdate(),
            $adherent->getCityName(),
            $adherent->getCountry(),
            $adherent->getPhone(),
            $adherent->isHost(),
            array_unique(array_map(function (CommitteeMembership $membership) {
                return $membership->getCommitteeUuid()->toString();
            }, $adherent->getMemberships()->toArray())),
            $adherent->hasSubscribedReferentsEmails()
        );
    }

    public static function createFromNewsletterSubscription(NewsletterSubscription $subscription): ManagedUser
    {
        return new self(
            self::TYPE_NEWSLETTER_SUBSCRIBER,
            $subscription->getId(),
            $subscription->getEmail(),
            $subscription->getCreatedAt(),
            $subscription->getPostalCode()
        );
    }

    public function isNewsletterSubscription()
    {
        return self::TYPE_NEWSLETTER_SUBSCRIBER === $this->type;
    }

    public function isAdherent()
    {
        return self::TYPE_ADHERENT === $this->type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function isEmailVisible(): bool
    {
        return $this->emailVisible;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getFullName(): ?string
    {
        if (!$this->firstName || !$this->lastName) {
            return '(marcheur)';
        }

        return $this->firstName.' '.$this->lastName;
    }

    public function getPartialName(): ?string
    {
        if (!$this->firstName || !$this->lastName) {
            return '(marcheur)';
        }

        if (!$this->lastName) {
            return $this->firstName;
        }

        return $this->firstName.' '.$this->getLastNameInitial();
    }

    public function getLastNameInitial()
    {
        $normalized = preg_replace('/[^a-z]+/', '', strtolower($this->lastName));

        return strlen($normalized) > 0 ? strtoupper($normalized[0]).'.' : '';
    }

    public function getBirthdate(): ?\DateTime
    {
        return $this->birthdate;
    }

    public function getAge(): ?string
    {
        if (!$this->firstName || !$this->lastName) {
            return '(marcheur)';
        }

        if (!$this->birthdate) {
            return '';
        }

        return (string) $this->birthdate->diff(new \DateTime())->format('%y');
    }

    public function getCity(): ?string
    {
        if (!$this->firstName || !$this->lastName) {
            return '(marcheur)';
        }

        return $this->city;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function isHost(): bool
    {
        return $this->isHost;
    }

    public function hasReferentsEmailsSubscription(): bool
    {
        return $this->referentsEmailsSubscription;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function getCommittees(): array
    {
        return $this->committees;
    }
}
