<?php

namespace App\Event\Request;

use App\Recaptcha\RecaptchaChallengeInterface;
use App\Recaptcha\RecaptchaChallengeTrait;
use App\Validator\Recaptcha as AssertRecaptcha;
use App\Validator\StrictEmail;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AssertRecaptcha(api="friendly_captcha")
 */
class EventInscriptionRequest implements RecaptchaChallengeInterface
{
    use RecaptchaChallengeTrait;

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
     * @Assert\NotBlank
     * @Assert\Range(max="-1 years")
     */
    public ?\DateTime $birthdate = null;

    /**
     * @AssertPhoneNumber(message="common.phone_number.invalid")
     */
    public ?PhoneNumber $phone = null;

    /**
     * @Assert\NotBlank
     * @Assert\Length(allowEmptyString=true, min=4, max=10)
     */
    public ?string $postalCode = null;

    public bool $allowNotifications = false;

    public ?string $utmSource = null;
    public ?string $utmCampaign = null;

    public array $qualities = [];

    public function __construct(
        public readonly string $sessionId,
        public readonly string $clientIp,
    ) {
    }
}
