<?php

declare(strict_types=1);

namespace App\BesoinDEurope\Inscription;

use App\Address\Address;
use App\Membership\MembershipRequest\MembershipInterface;
use App\Membership\MembershipSourceEnum;
use App\Validator\StrictEmail;
use App\Validator\UniqueMembership;
use App\ValueObject\Genders;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueMembership]
class InscriptionRequest implements MembershipInterface
{
    #[Assert\NotBlank]
    #[StrictEmail(dnsCheck: false)]
    public ?string $email = null;

    #[Assert\Choice(callback: [Genders::class, 'all'], message: 'common.invalid_choice')]
    #[Assert\NotBlank]
    public ?string $civility = null;

    #[Assert\Sequentially([
        new Assert\NotBlank(),
        new Assert\Length(min: 2, max: 50, minMessage: 'common.first_name.min_length', maxMessage: 'common.first_name.max_length'),
    ])]
    public ?string $firstName = null;

    #[Assert\Sequentially([
        new Assert\NotBlank(),
        new Assert\Length(min: 1, max: 50, minMessage: 'common.last_name.min_length', maxMessage: 'common.last_name.max_length'),
    ])]
    public ?string $lastName = null;

    #[Assert\Valid]
    public ?Address $address = null;

    #[Assert\NotNull(message: 'Veuillez cocher une réponse')]
    public ?bool $partyMembership = null;

    public bool $allowNotifications = false;

    public bool $acceptCgu = false;
    public bool $acceptCgu2 = false;

    public ?string $utmSource = null;
    public ?string $utmCampaign = null;

    public function getEmailAddress(): ?string
    {
        return $this->email;
    }

    public function getSource(): ?string
    {
        return MembershipSourceEnum::BESOIN_D_EUROPE;
    }
}
