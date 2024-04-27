<?php

namespace App\BesoinDEurope\Donation;

use App\Address\Address;
use App\Entity\Adherent;
use App\Recaptcha\RecaptchaChallengeInterface;
use App\Recaptcha\RecaptchaChallengeTrait;
use App\Validator\Recaptcha as AssertRecaptcha;
use App\Validator\StrictEmail;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AssertRecaptcha(api="friendly_captcha")
 */
class DonationRequest implements RecaptchaChallengeInterface
{
    use RecaptchaChallengeTrait;

    /**
     * @Assert\NotBlank
     * @Assert\Range(min=10, max=4600)
     */
    public ?int $amount = null;

    /**
     * @Assert\NotBlank
     * @StrictEmail(dnsCheck=false)
     */
    public ?string $email = null;

    /**
     * @Assert\NotBlank
     * @Assert\Choice(callback={"App\ValueObject\Genders", "all"}, message="common.invalid_choice")
     */
    public ?string $civility = null;

    /**
     * @Assert\NotBlank
     * @Assert\Length(
     *     allowEmptyString=true,
     *     min=2,
     *     max=50,
     *     minMessage="common.first_name.min_length",
     *     maxMessage="common.first_name.max_length"
     * )
     */
    public ?string $firstName = null;

    /**
     * @Assert\NotBlank
     * @Assert\Length(
     *     allowEmptyString=true,
     *     min=1,
     *     max=50,
     *     minMessage="common.last_name.min_length",
     *     maxMessage="common.last_name.max_length"
     * )
     */
    public ?string $lastName = null;

    /**
     * @Assert\Valid
     */
    public ?Address $address = null;

    /**
     * @Assert\Country
     */
    public ?string $nationality = null;

    public ?string $utmSource = null;
    public ?string $utmCampaign = null;

    public function updateFromAdherent(Adherent $user): void
    {
        $this->email = $user->getEmailAddress();
        $this->firstName = $user->getFirstName();
        $this->lastName = $user->getLastName();
        $this->civility = $user->getGender();
        $this->nationality = $user->getNationality();
        $this->address = Address::createFromAddress($user->getPostAddress());
    }

    public function hasAmount(): bool
    {
        return $this->amount >= 10;
    }
}
