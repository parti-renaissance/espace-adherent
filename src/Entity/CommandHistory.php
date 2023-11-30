<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommandHistoryRepository")
 */
class CommandHistory
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    public ?int $id = null;

    /**
     * @ORM\Column(enumType=CommandHistoryTypeEnum::class)
     */
    public CommandHistoryTypeEnum $type;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    public \DateTimeInterface $createdAt;

    public function __construct(CommandHistoryTypeEnum $type, \DateTimeInterface $createdAt = null)
    {
        $this->type = $type;
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
    }
}
