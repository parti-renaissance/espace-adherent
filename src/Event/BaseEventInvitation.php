<?php

namespace App\Event;

use App\Recaptcha\RecaptchaChallengeInterface;
use App\Recaptcha\RecaptchaChallengeTrait;
use Symfony\Component\Validator\Constraints as Assert;

abstract class BaseEventInvitation implements RecaptchaChallengeInterface
{
    use RecaptchaChallengeTrait;

    /**
     * @Assert\Email(message="common.email.invalid")
     * @Assert\NotBlank
     * @Assert\Length(max=255, maxMessage="common.email.max_length")
     */
    public $email = '';

    /**
     * @Assert\Type("string")
     * @Assert\NotBlank
     * @Assert\Length(max=50, maxMessage="common.first_name.max_length")
     */
    public $firstName = '';

    /**
     * @Assert\Type("string")
     * @Assert\NotBlank
     * @Assert\Length(max=50, maxMessage="common.first_name.max_length")
     */
    public $lastName = '';

    /**
     * @Assert\Length(max=200, maxMessage="event.invitation.message.max_length")
     */
    public $message = '';

    /**
     * @Assert\Type("array")
     * @Assert\Count(min=1, minMessage="event.invitation.guests.min")
     * @Assert\All({
     *     @Assert\Email(message="common.email.invalid"),
     *     @Assert\NotBlank,
     *     @Assert\Length(max=255, maxMessage="common.email.max_length")
     * })
     */
    public $guests = [];

    public function filter(): void
    {
        $this->guests = array_filter($this->guests);
    }
}
