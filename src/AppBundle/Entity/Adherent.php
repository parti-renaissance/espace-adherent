<?php

namespace AppBundle\Entity;

use AppBundle\Exception\AdherentTokenException;
use AppBundle\Exception\AdherentAlreadyEnabledException;
use AppBundle\Exception\AdherentException;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="adherents", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="adherents_uuid_unique", columns="uuid"),
 *   @ORM\UniqueConstraint(name="adherents_email_address_unique", columns="email_address")
 * })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AdherentRepository")
 */
class Adherent implements UserInterface
{
    const ENABLED = 'ENABLED';
    const DISABLED = 'DISABLED';

    use EntityIdentityTrait;
    use EntityCrudTrait;

    /**
     * @ORM\Column
     */
    private $password;

    /**
     * @ORM\Column(length=6)
     */
    private $gender;

    /**
     * @ORM\Column(length=50)
     */
    private $firstName;

    /**
     * @ORM\Column(length=50)
     */
    private $lastName;

    /**
     * @ORM\Column
     */
    private $emailAddress;

    /**
     * @ORM\Column(type="phone_number", nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="date")
     */
    private $birthdate;

    /**
     * The address street.
     *
     * @var string|null
     *
     * @ORM\Column(length=150, nullable=true)
     */
    private $address;

    /**
     * The address zip code.
     *
     * @var string|null
     *
     * @ORM\Column(length=15, nullable=true)
     */
    private $postalCode;

    /**
     * The address city code.
     *
     * @var string|null
     *
     * @ORM\Column(length=15, nullable=true)
     */
    private $city;

    /**
     * The address country code (ISO2).
     *
     * @var string
     *
     * @ORM\Column(length=2)
     */
    private $country;

    /**
     * @ORM\Column(length=20)
     */
    private $position;

    /**
     * @ORM\Column(length=10, options={"default"="DISABLED"})
     */
    private $status;

    /**
     * @ORM\Column(type="datetime")
     */
    private $registeredAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $activatedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastLoggedAt;

    public function __construct(
        UuidInterface $uuid,
        string $emailAddress,
        string $password,
        string $gender,
        string $firstName,
        string $lastName,
        \DateTime $birthdate,
        string $position,
        string $country = 'FR',
        string $address = null,
        string $city = null,
        string $postalCode = null,
        PhoneNumber $phone = null,
        string $status = self::DISABLED,
        string $registeredAt = 'now'
    ) {
        $this->uuid = $uuid;
        $this->password = $password;
        $this->gender = $gender;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->emailAddress = $emailAddress;
        $this->birthdate = $birthdate;
        $this->position = $position;
        $this->address = $address;
        $this->country = $country;
        $this->postalCode = $postalCode;
        $this->city = $city;
        $this->phone = $phone;
        $this->status = $status;
        $this->registeredAt = new \DateTimeImmutable($registeredAt);
    }

    public static function createUuid(string $email)
    {
        return Uuid::uuid5(Uuid::NAMESPACE_OID, $email);
    }

    public function __toString(): string
    {
        return $this->getFullName();
    }

    public function getRoles(): array
    {
        return ['ROLE_ADHERENT'];
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getSalt()
    {
    }

    public function getUsername(): string
    {
        return $this->emailAddress;
    }

    public function eraseCredentials()
    {
    }

    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    public function getFullName()
    {
        return $this->firstName.' '.$this->getLastName();
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function getGender()
    {
        return $this->gender;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function getBirthdate()
    {
        return $this->birthdate;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function getPostalCode()
    {
        return $this->postalCode;
    }

    public function isEnabled()
    {
        return self::ENABLED === $this->status;
    }

    /**
     * Returns the activation date.
     *
     * @return \DateTimeImmutable|null
     */
    public function getActivatedAt()
    {
        if ($this->activatedAt instanceof \DateTime) {
            $this->activatedAt = new \DateTimeImmutable($this->activatedAt->format('U'));
        }

        return $this->activatedAt;
    }

    /**
     * Activates the Adherent account with the provided activation token.
     *
     * @param AdherentActivationToken $token
     * @param string                  $timestamp
     *
     * @throws AdherentException
     * @throws AdherentTokenException
     */
    public function activate(AdherentActivationToken $token, string $timestamp = 'now')
    {
        if (self::ENABLED === $this->status) {
            throw new AdherentAlreadyEnabledException($this->uuid);
        }

        $token->consume($this);

        $this->status = self::ENABLED;
        $this->activatedAt = new \DateTimeImmutable($timestamp);
    }

    public function resetPassword(AdherentResetPasswordToken $token)
    {
        if (!$newPassword = $token->getNewPassword()) {
            throw new \InvalidArgumentException('Token must have a new password.');
        }

        $token->consume($this);

        $this->password = $newPassword;
    }

    /**
     * Records the adherent last login date and time.
     *
     * @param string|int $timestamp a valid date representation as a string or integer
     */
    public function recordLastLoginTime($timestamp = 'now')
    {
        $this->lastLoggedAt = new \DateTimeImmutable($timestamp);
    }

    /**
     * Returns the last login date and time of this adherent.
     *
     * @return \DateTimeImmutable|null
     */
    public function getLastLoggedAt()
    {
        if ($this->lastLoggedAt instanceof \DateTime) {
            $this->lastLoggedAt = new \DateTimeImmutable(
                $this->lastLoggedAt->format('Y-m-d H:i:s'),
                $this->lastLoggedAt->getTimezone()
            );
        }

        return $this->lastLoggedAt;
    }

    /**
     * Joins a committee as a HOST priviledged person.
     *
     * @param Committee $committee
     *
     * @return CommitteeMembership
     */
    public function hostCommittee(Committee $committee)
    {
        return $this->joinCommittee($committee, CommitteeMembership::COMMITTEE_HOST);
    }

    /**
     * Joins a committee as a simple FOLLOWER priviledged person.
     *
     * @param Committee $committee
     *
     * @return CommitteeMembership
     */
    public function followCommittee(Committee $committee)
    {
        return $this->joinCommittee($committee, CommitteeMembership::COMMITTEE_FOLLOWER);
    }

    private function joinCommittee(Committee $committee, string $privilege): CommitteeMembership
    {
        return CommitteeMembership::createForAdherent($this, $committee, $privilege);
    }
}
