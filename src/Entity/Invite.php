<?php

namespace App\Entity;

use App\Recaptcha\RecaptchaChallengeInterface;
use App\Recaptcha\RecaptchaChallengeTrait;
use App\Validator\Recaptcha as AssertRecaptcha;
use App\Validator\WasNotInvitedRecently as AssertWasNotInvitedRecently;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="invitations")
 * @ORM\Entity(repositoryClass="App\Repository\InviteRepository")
 *
 * @AssertWasNotInvitedRecently(
 *     emailField="email",
 *     since="24 hours",
 *     message="invitation.email.was_invited_recently"
 * )
 * @AssertRecaptcha
 */
class Invite implements RecaptchaChallengeInterface
{
    use EntityIdentityTrait;
    use RecaptchaChallengeTrait;

    /**
     * @var string
     *
     * @ORM\Column(length=50)
     */
    #[Assert\Sequentially([
        new Assert\NotBlank(),
        new Assert\Length(min: 2, max: 50, minMessage: 'common.first_name.min_length', maxMessage: 'common.first_name.max_length'),
    ])]
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(length=50)
     */
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 50, minMessage: 'common.last_name.min_length', maxMessage: 'common.last_name.max_length')]
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column
     */
    #[Assert\NotBlank]
    #[Assert\Email(message: 'common.email.invalid')]
    #[Assert\Length(max: 255, maxMessage: 'common.email.max_length')]
    private $email;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     */
    #[Assert\NotBlank(message: 'invitation.message.not_blank')]
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

    public function __toString()
    {
        return 'Invitation de '.$this->getSenderFullName().' à '.$this->email;
    }

    public static function createWithCaptcha(string $recaptcha): self
    {
        $invite = new self();
        $invite->setRecaptcha($recaptcha);

        return $invite;
    }

    public static function create(
        string $firstName,
        string $lastName,
        string $email,
        string $message,
        string $clientIp
    ): self {
        $invite = new self(Uuid::uuid4());
        $invite->firstName = $firstName;
        $invite->lastName = $lastName;
        $invite->email = $email;
        $invite->message = $message;
        $invite->clientIp = $clientIp;

        return $invite;
    }

    public function getSenderFullName(): string
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
     */
    public function setLastName($lastName): self
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
     */
    public function setFirstName($firstName): self
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
     */
    public function setEmail($email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string|null $message
     */
    public function setMessage($message): self
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

    public function setClientIp(string $clientIp): self
    {
        $this->clientIp = $clientIp;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }
}
