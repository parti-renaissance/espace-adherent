<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * This entity represents a committee membership.
 *
 * Each instance of CommitteeMembership means an Adherent is a member of a Committee.
 *
 * @ORM\Table(
 *   name="committees_memberships",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(
 *       name="adherent_has_joined_committee",
 *       columns={"adherent_uuid", "committee_uuid"}
 *     )
 *   },
 *   indexes={
 *     @ORM\Index(name="committees_memberships_role_idx", columns="privilege")
 *   }
 * )
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CommitteeMembershipRepository")
 */
class CommitteeMembership
{
    const COMMITTEE_HOST = 'HOST';
    const COMMITTEE_FOLLOWER = 'FOLLOWER';

    use EntityIdentityTrait;

    /**
     * The committee UUID.
     *
     * @var UuidInterface
     *
     * @ORM\Column(type="uuid")
     */
    private $committeeUuid;

    /**
     * @var Adherent|null
     *
     * @ORM\ManyToOne(targetEntity="Adherent")
     * @ORM\JoinColumn(name="adherent_uuid", fieldName="uuid")
     */
    private $adherent;

    /**
     * The privilege given to the member in the committee.
     *
     * Privilege is either HOST or FOLLOWER.
     *
     * @var string
     *
     * @ORM\Column(length=10)
     */
    private $privilege;

    /**
     * The date and time when the adherent joined the committee.
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $joinedAt;

    /**
     * Constructor.
     *
     * @param UuidInterface $uuid
     * @param UuidInterface $committeeUuid
     * @param Adherent      $adherent
     * @param string        $privilege
     */
    private function __construct(
        UuidInterface $uuid,
        UuidInterface $committeeUuid,
        Adherent $adherent,
        string $privilege = self::COMMITTEE_FOLLOWER
    ) {
        $this->uuid = $uuid;
        $this->committeeUuid = $committeeUuid;
        $this->adherent = $adherent;
        $this->privilege = $privilege;
        $this->joinedAt = new \DateTime();
    }

    public static function createForHost(UuidInterface $committeeUuid, Adherent $host): self
    {
        $committeeUuid = clone $committeeUuid;

        return new self(
            self::createUuid($host->getUuid(), $committeeUuid),
            $committeeUuid,
            $host,
            self::COMMITTEE_HOST
        );
    }

    /**
     * Creates a new membership relationship between an adherent and a committee.
     *
     * @param Adherent  $adherent
     * @param Committee $committee
     * @param string    $privilege
     *
     * @return CommitteeMembership
     */
    public static function createForAdherent(
        Adherent $adherent,
        Committee $committee,
        string $privilege = self::COMMITTEE_FOLLOWER
    ): self {
        $committeeUuid = clone $committee->getUuid();

        return new self(
            self::createUuid($adherent->getUuid(), $committeeUuid),
            $committeeUuid,
            $adherent,
            $privilege
        );
    }

    /**
     * Computes a unique UUID with the provided adherent and committee UUIDs.
     *
     * @param UuidInterface $adherentUuid
     * @param UuidInterface $committeUuid
     *
     * @return UuidInterface
     */
    private static function createUuid(UuidInterface $adherentUuid, UuidInterface $committeUuid): UuidInterface
    {
        $key = sha1(sprintf('%s|%s', $adherentUuid, $committeUuid));

        return Uuid::uuid5(Uuid::NAMESPACE_OID, $key);
    }

    /**
     * Returns whether or not this membership is a host priviledged membership.
     *
     * @return bool
     */
    public function isHostMember(): bool
    {
        return self::COMMITTEE_HOST === $this->privilege;
    }

    /**
     * Returns the adherent.
     *
     * @return Adherent|null
     */
    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    /**
     * Returns the adherent UUID.
     *
     * @return UuidInterface
     */
    public function getAdherentUuid(): UuidInterface
    {
        return $this->adherent->getUuid();
    }

    /**
     * Returns the adherent UUID.
     *
     * @return UuidInterface
     */
    public function getCommitteeUuid(): UuidInterface
    {
        return $this->committeeUuid;
    }
}
