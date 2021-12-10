<?php

namespace App\Membership\MembershipRequest;

use App\Validator\BannedAdherent;
use App\Validator\UniqueMembership as AssertUniqueMembership;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AssertUniqueMembership
 */
abstract class AbstractMembershipRequest implements MembershipInterface
{
    /**
     * @Assert\NotBlank
     * @Assert\Email(message="common.email.invalid")
     * @Assert\Length(max=255, maxMessage="common.email.max_length")
     * @BannedAdherent
     *
     * @Groups({"merbership:write"})
     */
    protected string $emailAddress = '';

    /**
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     allowEmptyString=false,
     *     minMessage="common.first_name.min_length",
     *     maxMessage="common.first_name.max_length"
     * )
     *
     * @Groups({"merbership:write"})
     */
    public ?string $firstName = null;

    /**
     * @Assert\IsTrue(message="common.conditions.not_accepted")
     *
     * @Groups({"merbership:write"})
     */
    public bool $cguAccepted = false;

    public ?bool $allowEmailNotifications = null;

    public ?bool $allowMobileNotifications = null;

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(string $emailAddress): void
    {
        $this->emailAddress = mb_strtolower($emailAddress);
    }
}
