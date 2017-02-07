<?php

namespace AppBundle\Entity;

use AppBundle\Exception\AdherentTokenException;
use AppBundle\Exception\AdherentAlreadyEnabledException;
use AppBundle\Exception\AdherentException;
use AppBundle\Geocoder\GeoPointInterface;
use AppBundle\Membership\AdherentEmailSubscription;
use AppBundle\Membership\MembershipRequest;
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
class Adherent implements UserInterface, GeoPointInterface
{
    const ENABLED = 'ENABLED';
    const DISABLED = 'DISABLED';

    use EntityIdentityTrait;
    use EntityCrudTrait;
    use EntityPostAddressTrait;

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

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $interests;

    /**
     * @ORM\Column(type="boolean")
     */
    private $mainEmailsSubscription = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $referentsEmailsSubscription = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $localHostEmailsSubscription = false;

    public function __construct(
        UuidInterface $uuid,
        string $emailAddress,
        string $password,
        string $gender,
        string $firstName,
        string $lastName,
        \DateTime $birthdate,
        string $position,
        PostAddress $postAddress,
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
        $this->postAddress = $postAddress;
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
        return $this->firstName.' '.$this->lastName;
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

    public function getAge(): int
    {
        return ($this->birthdate->diff(new \DateTime()))->y;
    }

    public function getPosition()
    {
        return $this->position;
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
            $this->activatedAt = new \DateTimeImmutable(
                $this->activatedAt->format('Y-m-d H:i:s'),
                $this->activatedAt->getTimezone()
            );
        }

        return $this->activatedAt;
    }

    public function changePassword(string $newPassword)
    {
        $this->password = $newPassword;
    }

    public function getEmailsSubscriptions(): array
    {
        $subscriptions = [];
        if ($this->mainEmailsSubscription) {
            $subscriptions[] = AdherentEmailSubscription::SUBSCRIBED_EMAILS_MAIN;
        }

        if ($this->referentsEmailsSubscription) {
            $subscriptions[] = AdherentEmailSubscription::SUBSCRIBED_EMAILS_REFERENTS;
        }

        if ($this->localHostEmailsSubscription) {
            $subscriptions[] = AdherentEmailSubscription::SUBSCRIBED_EMAILS_LOCAL_HOST;
        }

        return $subscriptions;
    }

    public function setEmailsSubscriptions(array $emailsSubscriptions)
    {
        $this->mainEmailsSubscription = in_array(AdherentEmailSubscription::SUBSCRIBED_EMAILS_MAIN, $emailsSubscriptions, true);
        $this->referentsEmailsSubscription = in_array(AdherentEmailSubscription::SUBSCRIBED_EMAILS_REFERENTS, $emailsSubscriptions, true);
        $this->localHostEmailsSubscription = in_array(AdherentEmailSubscription::SUBSCRIBED_EMAILS_LOCAL_HOST, $emailsSubscriptions, true);
    }

    public function hasSubscribedMainEmails(): bool
    {
        return $this->mainEmailsSubscription;
    }

    public function hasSubscribedReferentsEmails(): bool
    {
        return $this->referentsEmailsSubscription;
    }

    public function hasSubscribedLocalHostEmails(): bool
    {
        return $this->localHostEmailsSubscription;
    }

    public function enableCommitteesNotifications()
    {
        $this->localHostEmailsSubscription = true;
    }

    public function disableCommitteesNotifications()
    {
        $this->localHostEmailsSubscription = false;
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
     * @return array|null
     */
    public function getInterests()
    {
        return $this->interests;
    }

    /**
     * @return string
     */
    public function getInterestsAsJson()
    {
        return \GuzzleHttp\json_encode($this->interests, JSON_PRETTY_PRINT);
    }

    /**
     * @param array|null $interests
     */
    public function setInterests(array $interests = null)
    {
        $this->interests = $interests;
    }

    public function updateMembership(MembershipRequest $membership, PostAddress $postAddress)
    {
        $this->gender = $membership->gender;
        $this->firstName = $membership->firstName;
        $this->lastName = $membership->lastName;
        $this->birthdate = $membership->getBirthdate();
        $this->position = $membership->position;
        $this->phone = $membership->getPhone();

        if (!$this->postAddress->equals($postAddress)) {
            $this->postAddress = $postAddress;
        }
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
        $committee->incrementMembersCount();

        return CommitteeMembership::createForAdherent($this, $committee, $privilege);
    }

    /**
     * Returns the adherent post address.
     *
     * @return PostAddress
     */
    public function getPostAddress(): PostAddress
    {
        return $this->postAddress;
    }

    /**
     * Returns whether or not the current adherent is the same as the given one.
     *
     * @param Adherent $other
     *
     * @return bool
     */
    public function equals(self $other): bool
    {
        return $this->uuid->equals($other->getUuid());
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getRegisteredAt()
    {
        return $this->registeredAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setHasSubscribedMainEmails($mainEmailsSubscription)
    {
        $this->mainEmailsSubscription = $mainEmailsSubscription;
    }

    public function setHasSubscribedReferentsEmails($referentsEmailsSubscription)
    {
        $this->referentsEmailsSubscription = $referentsEmailsSubscription;
    }

    public function setHasSubscribedLocalHostEmails($localHostEmailsSubscription)
    {
        $this->localHostEmailsSubscription = $localHostEmailsSubscription;
    }
}
