<?php

namespace AppBundle\Entity;

use AppBundle\Utils\EmojisRemover;
use AppBundle\Validator\WasNotInvitedRecently as AssertWasNotInvitedRecently;
use AppBundle\Validator\Recaptcha as AssertRecaptcha;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="invitations")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\InvitationRepository")
 *
 * @AssertWasNotInvitedRecently(
 *     emailField="email",
 *     since="24 hours",
 *     message="invitation.email.was_invited_recently"
 * )
 */
class Invite
{
    use EntityIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column(length=50)
     *
     * @Assert\NotBlank(message="common.first_name.not_blank")
     * @Assert\Length(
     *   min=2,
     *   max=50,
     *   minMessage="common.first_name.min_length",
     *   maxMessage="common.first_name.max_length"
     * )
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(length=50)
     *
     * @Assert\NotBlank(message="common.last_name.not_blank")
     * @Assert\Length(
     *   min=2,
     *   max=50,
     *   minMessage="common.last_name.min_length",
     *   maxMessage="common.last_name.max_length"
     * )
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(length=100)
     *
     * @Assert\NotBlank(message="common.email.not_blank")
     * @Assert\Email(message="common.email.invalid")
     */
    private $email;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank(message="invitation.message.not_blank")
     */
    private $message;

    /**
     * @var string|null
     *
     * @ORM\Column(length=50, nullable=true)
     */
    private $clientIp;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="common.recaptcha.invalid_message")
     * @AssertRecaptcha
     */
    public $recaptcha;

    public function __construct(UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?: Uuid::uuid4();
        $this->createdAt = new \DateTime();
        $this->recaptcha = '';
    }

    public function __toString()
    {
        return 'Invitation de '.$this->getSenderFullName().' à '.$this->email;
    }

    public static function createWithCaptcha(string $recaptcha)
    {
        $invite = new self();
        $invite->recaptcha = $recaptcha;

        return $invite;
    }

    public static function create(
        string $firstName,
        string $lastName,
        string $email,
        string $message,
        string $clientIp
    ) {
        $invite = new static(Uuid::uuid4());
        $invite->setFirstName($firstName);
        $invite->setLastName($lastName);
        $invite->setEmail($email);
        $invite->setMessage($message);
        $invite->setClientIp($clientIp);

        return $invite;
    }

    public function getSenderFullName()
    {
        return $this->firstName.' '.$this->lastName;
    }

    /**
     * @return string|null
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string|null $lastName
     *
     * @return Invite
     */
    public function setLastName($lastName): Invite
    {
        $this->lastName = EmojisRemover::remove($lastName);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string|null $firstName
     *
     * @return Invite
     */
    public function setFirstName($firstName): Invite
    {
        $this->firstName = EmojisRemover::remove($firstName);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     *
     * @return Invite
     */
    public function setEmail($email): Invite
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param null|string $message
     *
     * @return Invite
     */
    public function setMessage($message): Invite
    {
        $this->message = EmojisRemover::remove($message);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getClientIp()
    {
        return $this->clientIp;
    }

    public function setClientIp(string $clientIp): Invite
    {
        $this->clientIp = $clientIp;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }
}
