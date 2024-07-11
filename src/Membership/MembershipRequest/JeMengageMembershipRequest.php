<?php

namespace App\Membership\MembershipRequest;

use App\Address\Address;
use App\Membership\MembershipSourceEnum;
use App\ValueObject\Genders;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class JeMengageMembershipRequest extends AbstractMembershipRequest
{
    #[Groups(['membership:write'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 50, minMessage: 'common.first_name.min_length', maxMessage: 'common.first_name.max_length')]
    public ?string $lastName = null;

    #[Groups(['membership:write'])]
    #[Assert\NotBlank(message: 'common.gender.not_blank')]
    #[Assert\Choice(callback: [Genders::class, 'all'], message: 'common.gender.invalid_choice')]
    public ?string $gender = null;

    #[Groups(['membership:write'])]
    #[Assert\NotBlank(message: 'common.birthdate.not_blank')]
    public ?\DateTimeInterface $birthdate = null;

    #[Groups(['membership:write'])]
    #[Assert\Country(message: 'common.nationality.invalid')]
    public ?string $nationality = null;

    /**
     * @AssertPhoneNumber
     */
    #[Groups(['membership:write'])]
    public ?PhoneNumber $phone = null;

    #[Groups(['membership:write'])]
    #[Assert\Valid]
    public ?Address $address = null;

    final public function getSource(): string
    {
        return MembershipSourceEnum::JEMENGAGE;
    }
}
