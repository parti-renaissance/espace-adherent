<?php

declare(strict_types=1);

namespace App\Entity\Geo;

use App\Entity\EntityTimestampableTrait;
use App\Repository\Geo\CityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CityRepository::class)]
#[ORM\Table(name: 'geo_city')]
class City implements ZoneableInterface
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
     * @var Department|null
     */
    #[ORM\JoinColumn(nullable: true)]
    #[ORM\ManyToOne(targetEntity: Department::class)]
    private $department;

    /**
     * @var District|null
     */
    #[ORM\JoinTable(name: 'geo_city_district')]
    #[ORM\ManyToMany(targetEntity: District::class, inversedBy: 'cities', cascade: ['persist'])]
    private $districts;

    /**
     * @var Canton[]|Collection
     */
    #[ORM\JoinTable(name: 'geo_city_canton')]
    #[ORM\ManyToMany(targetEntity: Canton::class, inversedBy: 'cities')]
    private $cantons;

    /**
     * @var CityCommunity|null
     */
    #[ORM\ManyToOne(targetEntity: CityCommunity::class)]
    private $cityCommunity;

    /**
     * @var City|null
     */
    #[ORM\ManyToOne(targetEntity: City::class)]
    private $replacement;

    public function __construct(string $code, string $name)
    {
        $this->code = $code;
        $this->name = $name;
        $this->districts = new ArrayCollection();
        $this->cantons = new ArrayCollection();
    }

    /**
     * @return string[]
     */
    public function getPostalCode(): array
    {
        return $this->postalCode ?: [];
    }

    public function getPostalCodeAsString(): string
    {
        return implode(', ', $this->getPostalCode());
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

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department): void
    {
        $this->department = $department;
    }

    public function getDistricts(): array
    {
        return $this->districts->toArray();
    }

    public function addDistrict(?District $district): void
    {
        $this->districts->contains($district) || $this->districts->add($district);
    }

    public function clearDistricts(): void
    {
        $this->districts->clear();
    }

    /**
     * @return Canton[]
     */
    public function getCantons(): array
    {
        return $this->cantons->toArray();
    }

    public function addCanton(Canton $canton): void
    {
        $this->cantons->contains($canton) || $this->cantons->add($canton);
    }

    public function clearCantons(): void
    {
        $this->cantons->clear();
    }

    public function getCityCommunity(): ?CityCommunity
    {
        return $this->cityCommunity;
    }

    public function setCityCommunity(?CityCommunity $cityCommunity): void
    {
        $this->cityCommunity = $cityCommunity;
    }

    public function getReplacement(): ?City
    {
        return $this->replacement;
    }

    public function setReplacement(?City $replacement): void
    {
        $this->replacement = $replacement;
    }

    public function getParents(): array
    {
        $toMerge = [];

        $reference = $this->findReferenceForParentalTree();

        if ($reference !== $this) {
            $toMerge[] = [$reference];
        }

        if ($reference->department) {
            $toMerge[] = [$reference->department];
            $toMerge[] = $reference->department->getParents();
        }

        if (self::CITY_PARIS_CODE !== $this->code) {
            $toMerge[] = $reference->getDistricts();
        }

        $toMerge[] = $reference->getCantons();

        if ($reference->cityCommunity) {
            $toMerge[] = [$reference->cityCommunity];
        }

        return $toMerge ? array_values(array_unique(array_merge(...$toMerge))) : [];
    }

    private function findReferenceForParentalTree(): City
    {
        if (!$this->replacement) {
            return $this;
        }

        return $this->replacement->findReferenceForParentalTree();
    }

    public function getZoneType(): string
    {
        return Zone::CITY;
    }
}
