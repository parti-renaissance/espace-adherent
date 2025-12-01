<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\Table(name: 'facebook_profiles')]
class FacebookProfile implements \Stringable
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @var string
     */
    #[ORM\Column(length: 30, unique: true)]
    private $facebookId;

    /**
     * @var string
     */
    #[ORM\Column]
    private $name = '';

    /**
     * @var string
     */
    #[ORM\Column(nullable: true)]
    private $emailAddress = '';

    /**
     * @var string
     */
    #[ORM\Column(length: 30)]
    private $gender = '';

    /**
     * @var array
     */
    #[ORM\Column(type: 'json')]
    private $ageRange = [];

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private $accessToken;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    private $hasAutoUploaded = false;

    public function __toString()
    {
        return 'Profil de '.$this->emailAddress;
    }

    public static function createUuid(string $facebookId): UuidInterface
    {
        return Uuid::uuid5(Uuid::NAMESPACE_OID, $facebookId);
    }

    public function getFacebookId(): string
    {
        return $this->facebookId;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken ?: '';
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

    public function hasAutoUploaded(): bool
    {
        return $this->hasAutoUploaded;
    }

    public function getAutoUploaded(): bool
    {
        return $this->hasAutoUploaded;
    }
}
