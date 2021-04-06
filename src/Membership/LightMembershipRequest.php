<?php

namespace App\Membership;

use App\Entity\Geo\Zone;
use App\Validator\BannedAdherent;
use App\Validator\UniqueMembership as AssertUniqueMembership;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AssertUniqueMembership
 */
class LightMembershipRequest implements MembershipInterface
{
    /**
     * @var string
     *
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     allowEmptyString=false,
     *     minMessage="common.first_name.min_length",
     *     maxMessage="common.first_name.max_length"
     * )
     */
    public $firstName;

    /**
     * @var Zone
     *
     * @Assert\NotBlank
     * @Assert\Valid
     */
    private $zone;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Email(message="common.email.invalid")
     * @Assert\Length(max=255, maxMessage="common.email.max_length")
     * @BannedAdherent
     */
    private $emailAddress;

    /**
     * @var string
     *
     * @Assert\NotBlank
     */
    private $source;

    /**
     * @var bool
     */
    private $coalitionSubscription = false;

    /**
     * @var bool
     */
    private $causeSubscription = false;

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(Zone $zone): void
    {
        $this->zone = $zone;
    }

    public function setEmailAddress(string $emailAddress): void
    {
        $this->emailAddress = mb_strtolower($emailAddress);
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress ?: '';
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(string $source): void
    {
        $this->source = $source;
    }

    public function isCoalitionSubscription(): bool
    {
        return $this->coalitionSubscription;
    }

    public function setCoalitionSubscription(bool $coalitionSubscription): void
    {
        $this->coalitionSubscription = $coalitionSubscription;
    }

    public function isCauseSubscription(): bool
    {
        return $this->causeSubscription;
    }

    public function setCauseSubscription(bool $causeSubscription): void
    {
        $this->causeSubscription = $causeSubscription;
    }
}
