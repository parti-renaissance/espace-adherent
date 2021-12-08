<?php

namespace App\Membership\MembershipRequest;

use App\Entity\Geo\Zone;
use App\Membership\MembershipSourceEnum;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class CoalitionMembershipRequest extends AbstractMembershipRequest
{
    /**
     * @Assert\NotBlank
     * @Assert\Valid
     *
     * @Groups({"merbership:write"})
     */
    public ?Zone $zone = null;

    /**
     * @Groups({"merbership:write"})
     */
    public bool $coalitionSubscription = false;

    /**
     * @Groups({"merbership:write"})
     */
    public bool $causeSubscription = false;

    final public function getSource(): string
    {
        return MembershipSourceEnum::COALITIONS;
    }
}
