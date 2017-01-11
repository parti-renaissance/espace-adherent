<?php

namespace AppBundle\Entity;

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
     * @var string
     *
     * @ORM\Column(length=150, nullable=true)
     */
    private $address;

    /**
     * The address zip code.
     *
     * @var string
     *
     * @ORM\Column(length=15, nullable=true)
     */
    private $postalCode;

    /**
     * The address city code.
     *
     * @var string
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
        string $country,
        string $address = null,
        string $city = null,
        string $postalCode = null,
        PhoneNumber $phone = null
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
        $this->registeredAt = new \DateTime();
    }

    public static function createUuid(string $email)
    {
        return Uuid::uuid5(Uuid::NAMESPACE_OID, $email);
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
}
