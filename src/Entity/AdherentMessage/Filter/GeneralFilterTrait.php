<?php

namespace App\Entity\AdherentMessage\Filter;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

trait GeneralFilterTrait
{
    /**
     * @var string|null
     */
    #[Groups(['audience_segment_read', 'audience_segment_write', 'adherent_message_update_filter'])]
    #[ORM\Column(nullable: true)]
    private $gender;

    /**
     * @var int|null
     */
    #[Assert\GreaterThanOrEqual(1)]
    #[Assert\LessThanOrEqual(200)]
    #[Groups(['audience_segment_read', 'audience_segment_write', 'adherent_message_update_filter'])]
    #[ORM\Column(type: 'integer', nullable: true)]
    private $ageMin;

    /**
     * @var int|null
     */
    #[Assert\GreaterThanOrEqual(1)]
    #[Assert\LessThanOrEqual(200)]
    #[Groups(['audience_segment_read', 'audience_segment_write', 'adherent_message_update_filter'])]
    #[ORM\Column(type: 'integer', nullable: true)]
    private $ageMax;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 255)]
    #[Groups(['audience_segment_read', 'audience_segment_write', 'adherent_message_update_filter'])]
    #[ORM\Column(nullable: true)]
    private $firstName;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 255)]
    #[Groups(['audience_segment_read', 'audience_segment_write', 'adherent_message_update_filter'])]
    #[ORM\Column(nullable: true)]
    private $lastName;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 255)]
    #[ORM\Column(nullable: true)]
    private $city;

    /**
     * @var array|null
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private $interests = [];

    /**
     * @var \DateTime|null
     */
    #[Groups(['audience_segment_read', 'audience_segment_write', 'adherent_message_update_filter'])]
    #[ORM\Column(type: 'date', nullable: true)]
    private $registeredSince;

    /**
     * @var \DateTime|null
     */
    #[Groups(['audience_segment_read', 'audience_segment_write', 'adherent_message_update_filter'])]
    #[ORM\Column(type: 'date', nullable: true)]
    private $registeredUntil;

    #[ORM\Column(nullable: true)]
    protected ?string $renaissanceMembership = null;

    #[Groups(['audience_segment_read', 'audience_segment_write', 'adherent_message_update_filter'])]
    #[ORM\Column(type: 'date', nullable: true)]
    public ?\DateTime $firstMembershipSince = null;

    #[Groups(['audience_segment_read', 'audience_segment_write', 'adherent_message_update_filter'])]
    #[ORM\Column(type: 'date', nullable: true)]
    public ?\DateTime $firstMembershipBefore = null;

    /**
     * @var \DateTime|null
     */
    #[Groups(['audience_segment_read', 'audience_segment_write', 'adherent_message_update_filter'])]
    #[ORM\Column(type: 'date', nullable: true)]
    private $lastMembershipSince;

    /**
     * @var \DateTime|null
     */
    #[Groups(['audience_segment_read', 'audience_segment_write', 'adherent_message_update_filter'])]
    #[ORM\Column(type: 'date', nullable: true)]
    private $lastMembershipBefore;

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }

    public function getAgeMin(): ?int
    {
        return $this->ageMin;
    }

    public function setAgeMin(?int $ageMin): void
    {
        $this->ageMin = $ageMin;
    }

    public function getAgeMax(): ?int
    {
        return $this->ageMax;
    }

    public function setAgeMax(?int $ageMax): void
    {
        $this->ageMax = $ageMax;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getInterests(): ?array
    {
        return $this->interests;
    }

    public function setInterests(?array $interests): void
    {
        $this->interests = $interests;
    }

    public function getCityAsArray(): array
    {
        return $this->city ? array_map('trim', explode(',', $this->city)) : [];
    }

    public function getRegisteredSince(): ?\DateTime
    {
        return $this->registeredSince;
    }

    public function setRegisteredSince(?\DateTime $registeredSince): void
    {
        $this->registeredSince = $registeredSince;
    }

    public function getRegisteredUntil(): ?\DateTime
    {
        return $this->registeredUntil;
    }

    public function setRegisteredUntil(?\DateTime $registeredUntil): void
    {
        $this->registeredUntil = $registeredUntil;
    }

    public function getLastMembershipSince(): ?\DateTime
    {
        return $this->lastMembershipSince;
    }

    public function setLastMembershipSince(?\DateTime $lastMembershipSince): void
    {
        $this->lastMembershipSince = $lastMembershipSince;
    }

    public function getLastMembershipBefore(): ?\DateTime
    {
        return $this->lastMembershipBefore;
    }

    public function setLastMembershipBefore(?\DateTime $lastMembershipBefore): void
    {
        $this->lastMembershipBefore = $lastMembershipBefore;
    }

    public function reset(): void
    {
        $this->gender = null;
        $this->ageMin = null;
        $this->ageMax = null;
        $this->firstName = null;
        $this->lastName = null;
        $this->city = null;
        $this->interests = [];
        $this->registeredSince = null;
        $this->registeredUntil = null;
        $this->firstMembershipSince = null;
        $this->firstMembershipBefore = null;
        $this->lastMembershipSince = null;
        $this->lastMembershipBefore = null;
    }
}
