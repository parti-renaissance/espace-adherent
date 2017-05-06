<?php

namespace AppBundle\Event;

use AppBundle\Entity\Adherent;
use Symfony\Component\Validator\Constraints as Assert;

class EventInvitation
{
    /**
     * @Assert\Email(message="common.email.invalid")
     * @Assert\NotBlank(message="common.email.not_blank")
     */
    public $email = '';

    /**
     * @Assert\Type("string")
     * @Assert\NotBlank(message="common.first_name.not_blank")
     * @Assert\Length(max=50, maxMessage="common.first_name.max_length")
     */
    public $firstName = '';

    /**
     * @Assert\Type("string")
     * @Assert\NotBlank(message="common.first_name.not_blank")
     * @Assert\Length(max=50, maxMessage="common.first_name.max_length")
     */
    public $lastName = '';

    /**
     * @Assert\Length(max=500, maxMessage="event.invitation.message.max_length")
     */
    public $message = '';

    /**
     * @Assert\Type("array")
     * @Assert\Count(min=1, minMessage="event.invitation.guests.min")
     * @Assert\All({
     *    @Assert\Email(message="common.email.invalid"),
     *    @Assert\NotBlank(message="common.email.not_blank")
     * })
     */
    public $guests = [];

    public static function createFromAdherent(?Adherent $adherent): self
    {
        $dto = new self();

        if ($adherent) {
            $dto->lastName = $adherent->getLastName();
            $dto->firstName = $adherent->getFirstName();
            $dto->email = $adherent->getEmailAddress();
        }

        return $dto;
    }

    public function filter(): void
    {
        $this->guests = array_filter($this->guests);
    }
}
