<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="donator_identifier")
 * @ORM\Entity(repositoryClass="App\Repository\DonatorIdentifierRepository")*
 */
class DonatorIdentifier
{
    /**
     * The unique auto incremented primary key.
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * The last unique account identifier of donators.
     *
     * @ORM\Column
     */
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
