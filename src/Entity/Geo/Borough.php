<?php

declare(strict_types=1);

namespace App\Entity\Geo;

use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'geo_borough')]
class Borough implements ZoneableInterface
{
    use GeoTrait;
    use EntityTimestampableTrait;

    /**
     * @var string[]|null
     */
    #[ORM\Column(type: 'simple_array', nullable: true)]
    private $postalCode;

    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private $population;

    /**
     * @var City
     */
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: City::class)]
    private $city;

    public function __construct(string $code, string $name, City $city)
    {
        $this->code = $code;
        $this->name = $name;
        $this->city = $city;
    }

    /**
     * @return string[]
     */
    public function getPostalCode(): array
    {
        return $this->postalCode ?: [];
    }

    /**
     * @param string[] $postalCode
     */
    public function setPostalCode(array $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function getPopulation(): ?int
    {
        return $this->population;
    }

    public function setPopulation(?int $population): void
    {
        $this->population = $population;
    }

    public function getCity(): City
    {
        return $this->city;
    }

    public function getParents(): array
    {
        $toMerge = [
            [$this->city],
            $this->city->getParents(),
        ];

        return array_values(array_unique(array_merge(...$toMerge)));
    }

    public function getZoneType(): string
    {
        return Zone::BOROUGH;
    }
}
