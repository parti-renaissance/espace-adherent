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
     * The adherent UUID.
     *
     * @var UuidInterface
     *
     * @ORM\Column(type="uuid")
     */
    private $adherentUuid;

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
     * @param UuidInterface $adherentUuid
     * @param string        $privilege
     * @param string        $joinedAt
     */
    private function __construct(
        UuidInterface $uuid,
        UuidInterface $committeeUuid,
        UuidInterface $adherentUuid,
        string $privilege = self::COMMITTEE_FOLLOWER,
        string $joinedAt = 'now'
    ) {
        $this->uuid = $uuid;
        $this->committeeUuid = $committeeUuid;
        $this->adherentUuid = $adherentUuid;
        $this->privilege = $privilege;
        $this->joinedAt = new \DateTime($joinedAt);
    }

    /**
     * Returns a new host membership for an adherent on a committee.
     *
     * @param UuidInterface $committeeUuid
     * @param UuidInterface $hostUuid
     * @param string        $joinedAt
     *
     * @return CommitteeMembership
     */
    public static function createForHost(
        UuidInterface $committeeUuid,
        UuidInterface $hostUuid,
        string $joinedAt = 'now'
    ): self {
        $hostUuid = clone $hostUuid;
        $committeeUuid = clone $committeeUuid;

        return new self(
            self::createUuid($hostUuid, $committeeUuid),
            $committeeUuid,
            $hostUuid,
            self::COMMITTEE_HOST,
            $joinedAt
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
        $adherentUuid = clone $adherent->getUuid();
        $committeeUuid = clone $committee->getUuid();

        return new self(
            self::createUuid($adherentUuid, $committeeUuid),
            $committeeUuid,
            $adherentUuid,
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
     * Returns the adherent UUID.
     *
     * @return UuidInterface
     */
    public function getAdherentUuid(): UuidInterface
    {
        return $this->adherentUuid;
    }
}
