<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Adherent;
use App\Recaptcha\RecaptchaApiClient;
use App\Recaptcha\RecaptchaChallengeInterface;
use App\Recaptcha\RecaptchaChallengeTrait;
use App\Validator\Recaptcha as AssertRecaptcha;
use Symfony\Component\Validator\Constraints as Assert;

#[AssertRecaptcha(api: RecaptchaApiClient::NAME, groups: ['em_event_invitation'])]
#[AssertRecaptcha(groups: ['re_event_invitation'])]
class EventInvitation implements RecaptchaChallengeInterface
{
    use RecaptchaChallengeTrait;

    #[Assert\Email(message: 'common.email.invalid')]
    #[Assert\Length(max: 255, maxMessage: 'common.email.max_length')]
    #[Assert\NotBlank]
    public $email = '';

    #[Assert\Length(max: 50, maxMessage: 'common.first_name.max_length')]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public $firstName = '';

    #[Assert\Length(max: 50, maxMessage: 'common.first_name.max_length')]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public $lastName = '';

    #[Assert\Length(max: 200, maxMessage: 'event.invitation.message.max_length')]
    public $message = '';

    #[Assert\All([
        new Assert\Email(message: 'common.email.invalid'),
        new Assert\NotBlank(),
        new Assert\Length(max: 255, maxMessage: 'common.email.max_length'),
    ])]
    #[Assert\Count(min: 1, minMessage: 'event.invitation.guests.min')]
    #[Assert\Type('array')]
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
