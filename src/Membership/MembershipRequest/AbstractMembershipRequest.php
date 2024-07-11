<?php

namespace App\Membership\MembershipRequest;

use App\Validator\BannedAdherent;
use App\Validator\UniqueMembership;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueMembership()]
abstract class AbstractMembershipRequest implements MembershipInterface
{
    #[Groups(['membership:write'])]
    #[Assert\NotBlank]
    #[Assert\Email(message: 'common.email.invalid')]
    #[Assert\Length(max: 255, maxMessage: 'common.email.max_length')]
    #[BannedAdherent]
    protected ?string $emailAddress = null;

    #[Groups(['membership:write'])]
    #[Assert\Length(min: 2, max: 50, minMessage: 'common.first_name.min_length', maxMessage: 'common.first_name.max_length')]
    public ?string $firstName = null;

    #[Groups(['membership:write'])]
    #[Assert\IsTrue(message: 'common.cgu.not_accepted', groups: ['Default', 'membership_request_amount'])]
    public bool $cguAccepted = false;

    #[Groups(['membership:write'])]
    public ?bool $allowEmailNotifications = null;

    #[Groups(['membership:write'])]
    public ?bool $allowMobileNotifications = null;

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(?string $emailAddress): void
    {
        $this->emailAddress = $emailAddress ? mb_strtolower($emailAddress) : $emailAddress;
    }
}
