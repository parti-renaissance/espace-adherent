<?php

declare(strict_types=1);

namespace App\Membership\MembershipRequest;

use App\Validator\BannedAdherent;
use App\Validator\UniqueMembership;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueMembership]
abstract class AbstractMembershipRequest implements MembershipInterface
{
    #[Assert\Email(message: 'common.email.invalid')]
    #[Assert\Length(max: 255, maxMessage: 'common.email.max_length')]
    #[Assert\NotBlank]
    #[BannedAdherent]
    #[Groups(['membership:write'])]
    protected ?string $emailAddress = null;

    #[Assert\Length(min: 2, max: 50, minMessage: 'common.first_name.min_length', maxMessage: 'common.first_name.max_length')]
    #[Groups(['membership:write'])]
    public ?string $firstName = null;

    #[Assert\IsTrue(message: 'common.cgu.not_accepted', groups: ['Default', 'membership_request_amount'])]
    #[Groups(['membership:write'])]
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
