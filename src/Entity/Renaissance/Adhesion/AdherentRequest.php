<?php

namespace App\Entity\Renaissance\Adhesion;

use App\Address\PostAddressFactory;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityPostAddressTrait;
use App\Entity\EntityTimestampableTrait;
use App\Membership\MembershipRequest\RenaissanceMembershipRequest;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Renaissance\AdherentRequestRepository")
 */
class AdherentRequest
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityPostAddressTrait;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     */
    public ?string $firstName = null;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     */
    public ?string $lastName = null;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     */
    public ?string $email = null;

    /**
     * @ORM\Column(type="integer")
     *
     * @Assert\NotBlank
     */
    public ?int $amount = null;

    /**
     * @ORM\Column(type="uuid")
     */
    public UuidInterface $token;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    public ?\DateTime $tokenUsedAt = null;

    /**
     * @ORM\Column(nullable=true)
     */
    public ?string $password = null;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    public bool $allowEmailNotifications = false;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    public bool $allowMobileNotifications = false;

    public function __construct(UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->token = Uuid::uuid4();
    }

    public static function create(RenaissanceMembershipRequest $command, ?string $password): self
    {
        $object = new self();

        $object->firstName = $command->firstName;
        $object->lastName = $command->lastName;
        $object->password = $password;
        $object->email = $command->getEmailAddress();
        $object->amount = $command->amount;
        $object->allowEmailNotifications = $command->allowEmailNotifications;
        $object->allowMobileNotifications = $command->allowMobileNotifications;
        $object->setPostAddress(PostAddressFactory::createFromAddress($command->getAddress()));

        return $object;
    }

    public function getFullName(): string
    {
        return sprintf('%s %s', $this->firstName, $this->lastName);
    }

    public function activate(): void
    {
        $this->tokenUsedAt = new \DateTime();
    }
}
