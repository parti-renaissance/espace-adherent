<?php

namespace App\Entity\Reporting;

use App\Entity\Administrator;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class AdministratorExportHistory
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var Administrator
     *
     * @ORM\ManyToOne(targetEntity=Administrator::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $administrator;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $routeName;

    /**
     * @var array
     *
     * @ORM\Column(type="json_array")
     */
    private $parameters;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $date;

    public function __construct(
        Administrator $administrator,
        string $routeName,
        array $parameters,
        \DateTimeInterface $date = null
    ) {
        $this->administrator = $administrator;
        $this->routeName = $routeName;
        $this->parameters = $parameters;
        $this->date = $date;
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

    public function getDateTime(): ?\DateTimeInterface
    {
        return $this->date;
    }
}
