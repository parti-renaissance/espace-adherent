<?php

namespace App\Membership\MembershipRequest;

use App\Address\Address;
use App\Membership\MembershipSourceEnum;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class JeMengageMembershipRequest extends AbstractMembershipRequest
{
    /**
     * @Assert\NotBlank
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
    public ?string $lastName = null;

    /**
     * @Assert\NotBlank(message="common.gender.not_blank")
     * @Assert\Choice(
     *     callback={"App\ValueObject\Genders", "all"},
     *     message="common.gender.invalid_choice",
     *     strict=true,
     * )
     *
     * @Groups({"merbership:write"})
     */
    public ?string $gender = null;

    /**
     * @Assert\NotBlank(message="common.birthdate.not_blank")
     *
     * @Groups({"merbership:write"})
     */
    public ?\DateTimeInterface $birthdate = null;

    /**
     * @Assert\NotBlank(message="adherent_profile.nationality.not_blank")
     * @Assert\Country(message="common.nationality.invalid")
     *
     * @Groups({"merbership:write"})
     */
    public ?string $nationality = null;

    /**
     * @AssertPhoneNumber(defaultRegion="FR")
     *
     * @Groups({"merbership:write"})
     */
    public ?PhoneNumber $phone = null;

    /**
     * @Assert\Valid
     *
     * @Groups({"merbership:write"})
     */
    public ?Address $address = null;

    final public function getSource(): string
    {
        return MembershipSourceEnum::JEMENGAGE;
    }
}
