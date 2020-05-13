<?php

namespace App\Event;

use App\Entity\Adherent;
use App\Validator\Recaptcha as AssertRecaptcha;
use Symfony\Component\Validator\Constraints as Assert;

class EventInvitation
{
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
     * @Assert\NotBlank(message="common.recaptcha.invalid_message")
     * @AssertRecaptcha
     */
    public $recaptcha;

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

    public static function createFromAdherent(?Adherent $adherent, ?string $recaptchaAnswer): self
    {
        $dto = new self();

        if ($adherent) {
            $dto->lastName = $adherent->getLastName();
            $dto->firstName = $adherent->getFirstName();
            $dto->email = $adherent->getEmailAddress();
        }

        $dto->recaptcha = $recaptchaAnswer;

        return $dto;
    }

    public function filter(): void
    {
        $this->guests = array_filter($this->guests);
    }
}
