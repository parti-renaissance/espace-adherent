<?php

namespace App\Entity\Geo;

use Doctrine\ORM\Mapping as ORM;

trait GeoTrait
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
     * @var string|null
     *
     * @ORM\Column(unique=true)
     */
    private $code;

    /**
     * @var string|null
     *
     * @ORM\Column
     */
    private $name;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $active = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function activate(bool $active = true): void
    {
        $this->active = $active;
    }

    private function sanitizeEntityList(array $entities): array
    {
        $unique = [];
        foreach (array_filter($entities) as $entity) {
            if (!\in_array($entity, $unique, true)) {
                $unique[] = $entity;
            }
        }

        return $unique;
    }
}
