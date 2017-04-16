<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(name="facebook_profiles", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="facebook_profile_uuid", columns="uuid"),
 *   @ORM\UniqueConstraint(name="facebook_profile_facebook_id", columns="facebook_id"),
 *   @ORM\UniqueConstraint(name="facebook_profile_email_address", columns="email_address")
 * })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FacebookProfileRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class FacebookProfile
{
    use EntityIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column(length=30)
     */
    private $facebookId;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $name = '';

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    private $emailAddress = '';

    /**
     * @var string
     *
     * @ORM\Column(length=30)
     */
    private $gender = '';

    /**
     * @var array
     *
     * @ORM\Column(type="json_array")
     */
    private $ageRange = [];

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function __toString()
    {
        return 'Profil de '.$this->emailAddress;
    }

    public static function createUuid(string $facebookId): UuidInterface
    {
        return Uuid::uuid5(Uuid::NAMESPACE_OID, $facebookId);
    }

    public static function createFromSDKResponse(array $data): self
    {
        $fbProfile = new self();
        $fbProfile->uuid = self::createUuid($data['id']);
        $fbProfile->facebookId = $data['id'];
        $fbProfile->emailAddress = $data['email'] ?: '';
        $fbProfile->name = $data['name'] ?: '';
        $fbProfile->ageRange = $data['age_range'] ?: [];
        $fbProfile->gender = $data['gender'] ?: '';

        return $fbProfile;
    }

    public function getFacebookId(): string
    {
        return $this->facebookId;
    }

    public function getName(): string
    {
        return $this->name ?: '';
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress ?: '';
    }

    public function getGender(): string
    {
        return $this->gender ?: '';
    }

    public function getAgeRange(): array
    {
        return $this->ageRange ?: [];
    }

    public function getAgeRangeAsString(): string
    {
        $parts = [];
        foreach ($this->ageRange as $k => $v) {
            $parts[] = $k.' : '.$v;
        }

        return implode(', ', $parts);
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }
}
