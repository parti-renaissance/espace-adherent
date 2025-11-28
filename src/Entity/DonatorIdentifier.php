<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\DonatorIdentifierRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DonatorIdentifierRepository::class)]
#[ORM\Table(name: 'donator_identifier')]
class DonatorIdentifier
{
    /**
     * The unique auto incremented primary key.
     */
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Id]
    private $id;

    /**
     * The last unique account identifier of donators.
     */
    #[ORM\Column]
    private $identifier;

    public function getId(): int
    {
        return $this->id;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }
}
