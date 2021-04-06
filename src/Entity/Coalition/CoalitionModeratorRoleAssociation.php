<?php

namespace App\Entity\Coalition;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table
 */
class CoalitionModeratorRoleAssociation
{
    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}
