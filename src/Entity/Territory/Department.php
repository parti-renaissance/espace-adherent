<?php

namespace AppBundle\Entity\Territory;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="territory_department")
 * @ORM\Entity()
 */
class Department
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
    private $code;

    /**
     * @var Region
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Territory\Region")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $region;

    public function __construct(Region $region, string $name, string $code)
    {
        $this->region = $region;
        $this->name = $name;
        $this->code = $code;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getRegion(): Region
    {
        return $this->region;
    }
}
