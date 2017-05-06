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
            $dto->setLastName($adherent->getLastName());
            $dto->setFirstName($adherent->getFirstName());
            $dto->setEmail($adherent->getEmailAddress());
        }

        return $dto;
    }

    public function filter()
    {
        $this->guests = array_filter($this->guests);
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }
}
