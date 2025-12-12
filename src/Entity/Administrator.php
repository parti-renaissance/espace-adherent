<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AdministratorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AdministratorRepository::class)]
#[ORM\Table(name: 'administrators')]
#[UniqueEntity(fields: ['emailAddress'])]
class Administrator implements \Stringable, UserInterface, TwoFactorInterface, PasswordAuthenticatedUserInterface, EquatableInterface
{
    use EntityZoneTrait;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    /**
     * @var string|null
     */
    #[Assert\Email]
    #[Assert\Length(max: 255, maxMessage: 'common.email.max_length')]
    #[Assert\NotBlank]
    #[ORM\Column(unique: true)]
    private $emailAddress;

    /**
     * @var string|null
     */
    #[ORM\Column]
    private $password;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private $googleAuthenticatorSecret;

    /**
     * @var AdministratorRole[]|Collection
     */
    #[ORM\InverseJoinColumn(name: 'administrator_role_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\JoinColumn(name: 'administrator_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\JoinTable(name: 'administrators_roles')]
    #[ORM\ManyToMany(targetEntity: AdministratorRole::class)]
    private Collection $administratorRoles;

    private array $sessionRoles = [];

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    private $activated = true;

    public function __construct()
    {
        $this->_init();
    }

    public function __toString()
    {
        return $this->emailAddress ?: '';
    }

    public function getRoles(): array
    {
        $roles = ['ROLE_ADMIN_DASHBOARD'];

        foreach ($this->administratorRoles as $administratorRole) {
            if (!$administratorRole->enabled) {
                continue;
            }

            $roles[] = $administratorRole->code;
        }

        return $roles;
    }

    public function getUserIdentifier(): string
    {
        return $this->emailAddress;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function eraseCredentials(): void
    {
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * @param string|null $emailAddress
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    /**
     * @param string|null $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function isActivated(): bool
    {
        return $this->activated;
    }

    public function setActivated(bool $activated): void
    {
        $this->activated = $activated;
    }

    public function getGoogleAuthenticatorSecret(): ?string
    {
        return $this->googleAuthenticatorSecret;
    }

    /**
     * @param string|null $googleAuthenticatorSecret
     */
    public function setGoogleAuthenticatorSecret($googleAuthenticatorSecret): void
    {
        $this->googleAuthenticatorSecret = $googleAuthenticatorSecret;
    }

    public function isGoogleAuthenticatorEnabled(): bool
    {
        return null !== $this->googleAuthenticatorSecret;
    }

    public function getGoogleAuthenticatorUsername(): string
    {
        return $this->emailAddress;
    }

    public function addAdministratorRole(AdministratorRole $administratorRole): void
    {
        if (!$this->administratorRoles->contains($administratorRole)) {
            $this->administratorRoles->add($administratorRole);
        }
    }

    public function removeAdministratorRole(AdministratorRole $administratorRole): void
    {
        $this->administratorRoles->removeElement($administratorRole);
    }

    public function getAdministratorRoles(): Collection
    {
        return $this->administratorRoles;
    }

    public function getAdministratorRoleCodes(): array
    {
        return array_map(function (AdministratorRole $administratorRole): string {
            return $administratorRole->code;
        }, $this->administratorRoles->toArray());
    }

    public function __serialize(): array
    {
        return [
            $this->id,
            $this->emailAddress,
            $this->getRoles(),
        ];
    }

    public function __unserialize(array $serialized): void
    {
        [$this->id, $this->emailAddress, $this->sessionRoles] = $serialized;
        $this->_init();
    }

    public function isEqualTo(UserInterface $user): bool
    {
        return $this->id === $user->getId() && $this->sessionRoles === $user->getRoles();
    }

    private function _init(): void
    {
        $this->administratorRoles = new ArrayCollection();
        $this->zones = new ArrayCollection();
    }
}
