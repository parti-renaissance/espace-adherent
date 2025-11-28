<?php

declare(strict_types=1);

namespace App\Entity\Reporting;

use App\Entity\Administrator;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class AdministratorExportHistory
{
    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    /**
     * @var Administrator
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Administrator::class)]
    private $administrator;

    /**
     * @var string
     */
    #[ORM\Column]
    private $routeName;

    /**
     * @var array
     */
    #[ORM\Column(type: 'json')]
    private $parameters;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $exportedAt;

    public function __construct(
        Administrator $administrator,
        string $routeName,
        array $parameters,
        ?\DateTimeInterface $exportedAt = null,
    ) {
        $this->administrator = $administrator;
        $this->routeName = $routeName;
        $this->parameters = $parameters;
        $this->exportedAt = $exportedAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdministrator(): Administrator
    {
        return $this->administrator;
    }

    public function getRouteName(): string
    {
        return $this->routeName;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getExportedAt(): ?\DateTimeInterface
    {
        return $this->exportedAt;
    }
}
