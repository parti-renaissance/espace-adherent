<?php

namespace App\Adhesion\Request;

use App\Membership\MembershipRequest\MembershipInterface;
use App\Membership\MembershipSourceEnum;
use App\Validator\StrictEmail;
use App\Validator\UniqueMembership;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueMembership(groups={"change-email"})
 */
class ValidateAccountRequest implements MembershipInterface
{
    /**
     * @Assert\NotBlank(groups={"validate-code"})
     * @Assert\Length(allowEmptyString=true, min=4, max=4, groups={"validate-code"})
     */
    public ?string $code = null;

    /**
     * @Assert\NotBlank(groups={"change-email"})
     * @StrictEmail(dnsCheck=false, groups={"change-email"})
     */
    public ?string $emailAddress = null;

    public function __construct(private readonly string $source = MembershipSourceEnum::RENAISSANCE)
    {
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }
}
