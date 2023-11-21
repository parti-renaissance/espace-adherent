<?php

namespace App\Event;

use App\Entity\Adherent;
use App\Recaptcha\RecaptchaChallengeInterface;
use App\Recaptcha\RecaptchaChallengeTrait;
use App\Validator\Recaptcha as AssertRecaptcha;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AssertRecaptcha(groups={"em_event_invitation"})
 * @AssertRecaptcha(api="friendly_captcha", groups={"re_event_invitation"})
 */
class EventInvitation implements RecaptchaChallengeInterface
{
    use RecaptchaChallengeTrait;

    #[Assert\Email(message: 'common.email.invalid')]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255, maxMessage: 'common.email.max_length')]
    public $email = '';

    #[Assert\Type('string')]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50, maxMessage: 'common.first_name.max_length')]
    public $firstName = '';

    #[Assert\Type('string')]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50, maxMessage: 'common.first_name.max_length')]
    public $lastName = '';

    #[Assert\Length(max: 200, maxMessage: 'event.invitation.message.max_length')]
    public $message = '';

    /**
     * @Assert\All({
     *     @Assert\Email(message="common.email.invalid"),
     *     @Assert\NotBlank,
     *     @Assert\Length(max=255, maxMessage="common.email.max_length")
     * })
     */
    #[Assert\Type('array')]
    #[Assert\Count(min: 1, minMessage: 'event.invitation.guests.min')]
    public $guests = [];

    public function filter(): void
    {
        $this->guests = array_filter($this->guests);
    }

    public static function createFromAdherent(?Adherent $adherent, ?string $recaptchaAnswer): self
    {
        $dto = new self();

        if ($adherent) {
            $dto->lastName = $adherent->getLastName();
            $dto->firstName = $adherent->getFirstName();
            $dto->email = $adherent->getEmailAddress();
        }

        $dto->setRecaptcha($recaptchaAnswer);

        return $dto;
    }
}
