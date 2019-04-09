<?php

namespace AppBundle\Entity\Territory;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="territory_city")
 * @ORM\Entity()
 */
class City
{
    /**
     * @var int|null
     *
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $postalCode;

    /**
     * @var Department
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Territory\Department")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $department;

    public function __construct(Department $department, string $name, string $postalCode)
    {
        $this->department = $department;
        $this->name = $name;
        $this->postalCode = $postalCode;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function getDepartment(): Department
    {
        return $this->department;
    }
}
