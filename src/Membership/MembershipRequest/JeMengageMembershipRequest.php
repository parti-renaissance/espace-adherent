<?php

declare(strict_types=1);

namespace App\Membership\MembershipRequest;

use App\Address\Address;
use App\Membership\MembershipSourceEnum;
use App\ValueObject\Genders;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class JeMengageMembershipRequest extends AbstractMembershipRequest
{
    #[Assert\Length(min: 2, max: 50, minMessage: 'common.first_name.min_length', maxMessage: 'common.first_name.max_length')]
    #[Assert\NotBlank]
    #[Groups(['membership:write'])]
    public ?string $lastName = null;

    #[Assert\Choice(callback: [Genders::class, 'all'], message: 'common.gender.invalid_choice')]
    #[Assert\NotBlank(message: 'common.gender.not_blank')]
    #[Groups(['membership:write'])]
    public ?string $gender = null;

    #[Assert\NotBlank(message: 'common.birthdate.not_blank')]
    #[Groups(['membership:write'])]
    public ?\DateTimeInterface $birthdate = null;

    #[Assert\Country(message: 'common.nationality.invalid')]
    #[Groups(['membership:write'])]
    public ?string $nationality = null;

    #[AssertPhoneNumber]
    #[Groups(['membership:write'])]
    public ?PhoneNumber $phone = null;

    #[Assert\Valid]
    #[Groups(['membership:write'])]
    public ?Address $address = null;

    final public function getSource(): string
    {
        return MembershipSourceEnum::JEMENGAGE;
    }
}
