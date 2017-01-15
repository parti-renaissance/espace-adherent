<?php

namespace AppBundle\Entity;

use AppBundle\Validator\WasNotInvitedRecently;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="invitations")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\InvitationRepository")
 *
 * @WasNotInvitedRecently(
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

    public function __construct(UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?: Uuid::uuid4();
        $this->createdAt = new \DateTime();
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
        $this->lastName = $lastName;

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
        $this->firstName = $firstName;

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
        $this->message = $message;

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
