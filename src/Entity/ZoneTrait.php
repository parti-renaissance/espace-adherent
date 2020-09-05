<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait ZoneTrait
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
