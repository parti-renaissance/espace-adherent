<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Collection\CommitteeMembershipCollection;
use AppBundle\Exception\AdherentAlreadyEnabledException;
use AppBundle\Exception\AdherentException;
use AppBundle\Exception\AdherentTokenException;
use AppBundle\Geocoder\GeoPointInterface;
use AppBundle\Membership\AdherentEmailSubscription;
use AppBundle\Membership\MembershipRequest;
use AppBundle\ValueObject\Genders;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\Encoder\EncoderAwareInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="adherents", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="adherents_uuid_unique", columns="uuid"),
 *   @ORM\UniqueConstraint(name="adherents_email_address_unique", columns="email_address")
 * })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AdherentRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class Adherent implements UserInterface, GeoPointInterface, EncoderAwareInterface
{
    const ENABLED = 'ENABLED';
    const DISABLED = 'DISABLED';

    use EntityIdentityTrait;
    use EntityCrudTrait;
    use EntityPostAddressTrait;
    use EntityPersonNameTrait;

    /**
     * @ORM\Column(nullable=true)
     */
    private $password;

    /**
     * @ORM\Column(nullable=true)
     */
    private $oldPassword;

    /**
     * @ORM\Column(length=6)
     */
    private $gender;

    /**
     * @ORM\Column
     */
    private $emailAddress;

    /**
     * @ORM\Column(type="phone_number", nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="date", nullable=true)
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
     * @ORM\Column(type="json_array")
     */
    private $interests = [];

    /**
     * @ORM\Column(type="boolean")
     */
    private $mainEmailsSubscription = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private $referentsEmailsSubscription = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private $localHostEmailsSubscription = true;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $legislativeCandidate;

    /**
     * @ORM\Embedded(class="ManagedArea", columnPrefix="managed_area_")
     *
     * @var ManagedArea
     */
    private $managedArea;

    /**
     * @var ProcurationManagedArea|null
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\ProcurationManagedArea", mappedBy="adherent", cascade={"persist"})
     */
    private $procurationManagedArea;

    /**
     * @var CommitteeMembership[]|Collection
     *
     * @ORM\OneToMany(targetEntity="CommitteeMembership", mappedBy="adherent")
     */
    private $memberships;

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
        $this->legislativeCandidate = false;
        $this->registeredAt = new \DateTime($registeredAt);
        $this->memberships = new ArrayCollection();
    }

    public static function createUuid(string $email): UuidInterface
    {
        return Uuid::uuid5(Uuid::NAMESPACE_OID, $email);
    }

    public function getRoles(): array
    {
        $roles = ['ROLE_ADHERENT'];

        if ($this->isReferent()) {
            $roles[] = 'ROLE_REFERENT';
        }

        if ($this->isProcurationManager()) {
            $roles[] = 'ROLE_PROCURATION_MANAGER';
        }

        if ($this->legislativeCandidate) {
            $roles[] = 'ROLE_LEGISLATIVE_CANDIDATE';
        }

        return $roles;
    }

    public function getType(): string
    {
        if ($this->isReferent()) {
            return 'REFERENT';
        }

        if ($this->isHost()) {
            return 'HOST';
        }

        return 'ADHERENT';
    }

    public function hasAdvancedPrivileges(): bool
    {
        return $this->isReferent() || $this->isProcurationManager() || $this->isHost();
    }

    public function getPassword(): string
    {
        return !$this->password ? $this->oldPassword : $this->password;
    }

    public function hasLegacyPassword(): bool
    {
        return null !== $this->oldPassword;
    }

    public function getEncoderName(): ?string
    {
        if ($this->hasLegacyPassword()) {
            return 'legacy_encoder';
        }

        return null;
    }

    public function getSalt()
    {
    }

    public function getUsername(): string
    {
        return $this->emailAddress;
    }

    public function eraseCredentials(): void
    {
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    public function getGender(): string
    {
        return $this->gender;
    }

    public function isFemale(): bool
    {
        return Genders::FEMALE === $this->gender;
    }

    public function getBirthdate(): ?\DateTime
    {
        return $this->birthdate;
    }

    public function getAge(): ?int
    {
        return $this->birthdate ? ($this->birthdate->diff(new \DateTime()))->y : null;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function isEnabled(): bool
    {
        return self::ENABLED === $this->status;
    }

    public function getActivatedAt(): ?\DateTime
    {
        return $this->activatedAt;
    }

    public function changePassword(string $newPassword): void
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

    public function setEmailsSubscriptions(array $emailsSubscriptions): void
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

    public function enableCommitteesNotifications(): void
    {
        $this->localHostEmailsSubscription = true;
    }

    public function disableCommitteesNotifications(): void
    {
        $this->localHostEmailsSubscription = false;
    }

    /**
     * Activates the Adherent account with the provided activation token.
     *
     * @throws AdherentException
     * @throws AdherentTokenException
     */
    public function activate(AdherentActivationToken $token, string $timestamp = 'now'): void
    {
        if ($this->activatedAt) {
            throw new AdherentAlreadyEnabledException($this->uuid);
        }

        $token->consume($this);

        $this->status = self::ENABLED;
        $this->activatedAt = new \DateTime($timestamp);
    }

    /**
     * Resets the Adherent password using a reset pasword token.
     *
     * @throws \InvalidArgumentException
     * @throws AdherentException
     * @throws AdherentTokenException
     */
    public function resetPassword(AdherentResetPasswordToken $token): void
    {
        if (!$newPassword = $token->getNewPassword()) {
            throw new \InvalidArgumentException('Token must have a new password.');
        }

        $token->consume($this);

        $this->clearOldPassword();
        $this->password = $newPassword;
    }

    public function clearOldPassword(): void
    {
        $this->oldPassword = null;
    }

    public function migratePassword(string $newEncodedPassword): void
    {
        $this->password = $newEncodedPassword;
    }

    /**
     * Records the adherent last login date and time.
     *
     * @param string|int $timestamp a valid date representation as a string or integer
     */
    public function recordLastLoginTime($timestamp = 'now'): void
    {
        $this->lastLoggedAt = new \DateTime($timestamp);
    }

    /**
     * Returns the last login date and time of this adherent.
     */
    public function getLastLoggedAt(): ?\DateTime
    {
        return $this->lastLoggedAt;
    }

    /**
     * @return array|null
     */
    public function getInterests(): ?array
    {
        return $this->interests;
    }

    public function getInterestsAsJson(): ?string
    {
        return \GuzzleHttp\json_encode($this->interests, JSON_PRETTY_PRINT);
    }

    /**
     * @param array|null $interests
     */
    public function setInterests(array $interests = null): void
    {
        $this->interests = $interests;
    }

    public function updateMembership(MembershipRequest $membership, PostAddress $postAddress): void
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
     * Joins a committee as a SUPERVISOR privileged person.
     */
    public function superviseCommittee(Committee $committee, string $subscriptionDate = 'now'): CommitteeMembership
    {
        return $this->joinCommittee($committee, CommitteeMembership::COMMITTEE_SUPERVISOR, $subscriptionDate);
    }

    /**
     * Joins a committee as a HOST privileged person.
     */
    public function hostCommittee(Committee $committee, string $subscriptionDate = 'now'): CommitteeMembership
    {
        return $this->joinCommittee($committee, CommitteeMembership::COMMITTEE_HOST, $subscriptionDate);
    }

    /**
     * Joins a committee as a simple FOLLOWER privileged person.
     */
    public function followCommittee(Committee $committee, string $subscriptionDate = 'now'): CommitteeMembership
    {
        return $this->joinCommittee($committee, CommitteeMembership::COMMITTEE_FOLLOWER, $subscriptionDate);
    }

    private function joinCommittee(Committee $committee, string $privilege, string $subscriptionDate): CommitteeMembership
    {
        $committee->incrementMembersCount();

        return CommitteeMembership::createForAdherent($committee->getUuid(), $this, $privilege, $subscriptionDate);
    }

    public function getPostAddress(): PostAddress
    {
        return $this->postAddress;
    }

    /**
     * Returns whether or not the current adherent is the same as the given one.
     */
    public function equals(self $other): bool
    {
        return $this->uuid->equals($other->getUuid());
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getRegisteredAt(): ?\DateTime
    {
        return $this->registeredAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setHasSubscribedMainEmails($mainEmailsSubscription): void
    {
        $this->mainEmailsSubscription = $mainEmailsSubscription;
    }

    public function setHasSubscribedReferentsEmails($referentsEmailsSubscription): void
    {
        $this->referentsEmailsSubscription = $referentsEmailsSubscription;
    }

    public function setHasSubscribedLocalHostEmails($localHostEmailsSubscription): void
    {
        $this->localHostEmailsSubscription = $localHostEmailsSubscription;
    }

    public function getManagedArea(): ?ManagedArea
    {
        return $this->managedArea;
    }

    public function setManagedArea(ManagedArea $managedArea): void
    {
        $this->managedArea = $managedArea;
    }

    public function getProcurationManagedArea(): ?ProcurationManagedArea
    {
        return $this->procurationManagedArea;
    }

    public function setProcurationManagedArea(ProcurationManagedArea $procurationManagedArea = null): void
    {
        $this->procurationManagedArea = $procurationManagedArea;
    }

    public function setReferent(array $codes, string $markerLatitude, string $markerLongitude): void
    {
        $this->managedArea = new ManagedArea();
        $this->managedArea->setCodes($codes);
        $this->managedArea->setMarkerLatitude($markerLatitude);
        $this->managedArea->setMarkerLongitude($markerLongitude);
    }

    public function isReferent(): bool
    {
        return $this->managedArea instanceof ManagedArea && !empty($this->managedArea->getCodes());
    }

    public function getManagedAreaCodesAsString(): ?string
    {
        return $this->managedArea->getCodesAsString();
    }

    public function getManagedAreaMarkerLatitude(): ?string
    {
        return $this->managedArea->getMarkerLatitude();
    }

    public function getManagedAreaMarkerLongitude(): ?string
    {
        return $this->managedArea->getMarkerLongitude();
    }

    public function isProcurationManager(): bool
    {
        return $this->procurationManagedArea instanceof ProcurationManagedArea && !empty($this->procurationManagedArea->getCodes());
    }

    public function getProcurationManagedAreaCodesAsString(): ?string
    {
        if (!$this->procurationManagedArea) {
            return '';
        }

        return $this->procurationManagedArea->getCodesAsString();
    }

    public function setProcurationManagedAreaCodesAsString(string $codes = null): void
    {
        if (!$codes) {
            return;
        }

        if (!$this->procurationManagedArea) {
            $this->procurationManagedArea = new ProcurationManagedArea();
            $this->procurationManagedArea->setAdherent($this);
        }

        $this->procurationManagedArea->setCodesAsString((string) $codes);
    }

    final public function getMemberships(): CommitteeMembershipCollection
    {
        if ($this->memberships instanceof Collection) {
            if (!$this->memberships instanceof CommitteeMembershipCollection) {
                $this->memberships = new CommitteeMembershipCollection($this->memberships->toArray());
            }
        } else {
            $this->memberships = new CommitteeMembershipCollection((array) $this->memberships);
        }

        return $this->memberships;
    }

    public function getMembershipFor(Committee $committee): ?CommitteeMembership
    {
        foreach ($this->memberships as $membership) {
            if ($membership->matches($this, $committee)) {
                return $membership;
            }
        }

        return null;
    }

    public function isHost(): bool
    {
        return $this->getMemberships()->countCommitteeHostMemberships() >= 1;
    }

    public function isHostOf(Committee $committee): bool
    {
        if (!$membership = $this->getMembershipFor($committee)) {
            return false;
        }

        return $membership->canHostCommittee();
    }

    public function isSupervisorOf(Committee $committee): bool
    {
        if (!$membership = $this->getMembershipFor($committee)) {
            return false;
        }

        return $membership->isSupervisor();
    }

    public function isLegislativeCandidate(): bool
    {
        return $this->legislativeCandidate;
    }

    public function setLegislativeCandidate(bool $candidate): void
    {
        $this->legislativeCandidate = $candidate;
    }
}
