<?php

namespace App\Entity\ElectedRepresentative;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="elected_representative_zone_category",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="elected_representative_zone_category_name_unique", columns={"name"})
 *     })
 * )
 */
class ZoneCategory
{
    public const CITY = 'Ville';
    public const EPCI = 'EPCI';
    public const DEPARTMENT = 'Département';
    public const REGION = 'Région';
    public const DISTRICT = 'Circonscription';
    public const CORSICA = 'Corse';
    public const FOF = 'FDE';
    public const COUNTRY = 'Country';
    public const FOREIGN_DISTRICT = 'Foreign District';
    public const CONSULAR_DISTRICT = 'Consular District';

    public const ZONES = [
        'CITY_COUNCIL' => [self::CITY],
        'EPCI_MEMBER' => [self::EPCI],
        'DEPARTMENTAL_COUNCIL' => [self::DEPARTMENT],
        'REGIONAL_COUNCIL' => [
            self::REGION,
            self::DEPARTMENT,
        ],
        'CORSICA_ASSEMBLY_MEMBER' => [self::CORSICA],
        'DEPUTY' => [self::DISTRICT],
        'SENATOR' => [
            self::DISTRICT,
            self::DEPARTMENT,
            self::FOF,
        ],
        'EURO_DEPUTY' => [
            self::DEPARTMENT,
            self::DISTRICT,
        ],
        'FDE_DEPUTY' => [
            self::FOREIGN_DISTRICT,
        ],
        'CONSULAR_COUNCIL' => [
            self::CONSULAR_DISTRICT,
        ],
    ];

    public const ALL = [
        self::CITY,
        self::EPCI,
        self::DEPARTMENT,
        self::REGION,
        self::DISTRICT,
        self::CORSICA,
        self::FOF,
        self::COUNTRY,
        self::FOREIGN_DISTRICT,
        self::CONSULAR_DISTRICT,
    ];

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
