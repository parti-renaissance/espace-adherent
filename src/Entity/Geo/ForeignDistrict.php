<?php

declare(strict_types=1);

namespace App\Entity\Geo;

use App\Entity\EntityTimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'geo_foreign_district')]
class ForeignDistrict implements ZoneableInterface
{
    use GeoTrait;
    use EntityTimestampableTrait;

    /**
     * @var int
     */
    #[ORM\Column(type: 'smallint')]
    private $number;

    /**
     * @var CustomZone
     */
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: CustomZone::class)]
    private $customZone;

    /**
     * @var Collection|Country[]
     */
    #[ORM\OneToMany(mappedBy: 'foreignDistrict', targetEntity: Country::class)]
    private $countries;

    public function __construct(string $code, string $name, int $number, CustomZone $customZone)
    {
        $this->code = $code;
        $this->name = $name;
        $this->number = $number;
        $this->customZone = $customZone;
        $this->countries = new ArrayCollection();
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * @return Country[]
     */
    public function getCountries(): array
    {
        return $this->countries->toArray();
    }

    public function addCountry(Country $country): void
    {
        $this->countries->contains($country) || $this->countries->add($country);
    }

    public function removeCountry(Country $country): void
    {
        $this->countries->removeElement($country);
    }

    public function getParents(): array
    {
        $toMerge = [
            [$this->customZone],
            $this->customZone->getParents(),
        ];

        return array_values(array_unique(array_merge(...$toMerge)));
    }

    public function getZoneType(): string
    {
        return Zone::FOREIGN_DISTRICT;
    }
}
