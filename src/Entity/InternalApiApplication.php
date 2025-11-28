<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\InternalApiApplicationRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: InternalApiApplicationRepository::class)]
class InternalApiApplication
{
    use EntityIdentityTrait;

    /**
     * @var string
     */
    #[Assert\Length(max: 200, maxMessage: 'internal_api_application.validation.application_name_length')]
    #[ORM\Column(length: 200)]
    private $applicationName;

    /**
     * @var string
     */
    #[Assert\NotBlank]
    #[Assert\Url]
    #[ORM\Column(length: 200)]
    private $hostname;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $scopeRequired;

    public function __construct(
        string $applicationName,
        string $hostname,
        bool $scopeRequired = false,
        ?UuidInterface $uuid = null,
    ) {
        $this->applicationName = $applicationName;
        $this->hostname = $hostname;
        $this->scopeRequired = $scopeRequired;
        $this->uuid = $uuid ?: Uuid::uuid4();
    }

    public function getApplicationName(): string
    {
        return $this->applicationName;
    }

    public function setApplicationName(string $applicationName): void
    {
        $this->applicationName = $applicationName;
    }

    public function getHostname(): string
    {
        return $this->hostname;
    }

    public function setHostname(string $hostname): void
    {
        $this->hostname = $hostname;
    }

    public function isScopeRequired(): ?bool
    {
        return $this->scopeRequired;
    }
}
