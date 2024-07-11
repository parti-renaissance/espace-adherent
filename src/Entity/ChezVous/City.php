<?php

namespace App\Entity\ChezVous;

use App\ChezVous\MeasureChoiceLoader;
use App\Entity\AlgoliaIndexedEntityInterface;
use App\Repository\ChezVous\CityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CityRepository::class)]
#[ORM\Table(name: 'chez_vous_cities')]
#[UniqueEntity(fields: ['slug'])]
#[UniqueEntity(fields: ['inseeCode'])]
class City implements AlgoliaIndexedEntityInterface
{
    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    /**
     * @var string|null
     */
    #[Assert\Length(max: '100')]
    #[Assert\NotBlank]
    #[ORM\Column(length: 100)]
    private $name;

    /**
     * @var string[]|null
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: 'json')]
    private $postalCodes = [];

    /**
     * @var string|null
     */
    #[Assert\Length(max: '10')]
    #[Assert\NotBlank]
    #[ORM\Column(length: 10, unique: true)]
    private $inseeCode;

    /**
     * @var float|null
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: 'geo_point')]
    private $latitude;

    /**
     * @var float|null
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: 'geo_point')]
    private $longitude;

    /**
     * @var string|null
     */
    #[Assert\Length(max: '100')]
    #[Assert\NotBlank]
    #[ORM\Column(length: 100, unique: true)]
    private $slug;

    /**
     * @var Department|null
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Department::class, fetch: 'EAGER', inversedBy: 'cities')]
    private $department;

    /**
     * @var Measure[]|Collection
     */
    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'city', targetEntity: Measure::class, cascade: ['all'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    private $measures;

    /**
     * @var Marker[]|Collection
     */
    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'city', targetEntity: Marker::class, cascade: ['all'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    private $markers;

    public function __construct(
        ?Department $department = null,
        ?string $name = null,
        ?array $postalCodes = null,
        ?string $inseeCode = null,
        ?string $slug = null,
        ?float $latitude = null,
        ?float $longitude = null
    ) {
        $this->department = $department;
        $this->name = $name;
        $this->inseeCode = $inseeCode ? self::normalizeCode($inseeCode) : null;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->slug = $slug;
        $this->measures = new ArrayCollection();
        $this->markers = new ArrayCollection();

        if ($postalCodes) {
            foreach ($postalCodes as $postalCode) {
                $this->addPostalCode($postalCode);
            }
        }
    }

    public function __toString()
    {
        return sprintf('%s (%s)', $this->name, $this->exportPostalCodes());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getPostalCodes(): ?array
    {
        return $this->postalCodes;
    }

    public function addPostalCode(string $postalCode): void
    {
        $postalCode = self::normalizeCode($postalCode);

        if (!\in_array($postalCode, $this->postalCodes, true)) {
            $this->postalCodes[] = $postalCode;
        }
    }

    public function removePostalCode(string $postalCode): void
    {
        if (false !== $key = array_search($postalCode, $this->postalCodes)) {
            unset($this->postalCodes[$key]);
        }
    }

    public function getInseeCode(): ?string
    {
        return $this->inseeCode;
    }

    public function setInseeCode(?string $inseeCode): void
    {
        $this->inseeCode = $inseeCode;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): void
    {
        $this->latitude = $latitude;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): void
    {
        $this->longitude = $longitude;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department): void
    {
        $this->department = $department;
    }

    public function getMeasures(): Collection
    {
        $typesOrder = array_keys(MeasureChoiceLoader::getTypeKeysMap());

        $measures = array_values($this->measures->toArray());

        usort($measures, function (Measure $measure1, Measure $measure2) use ($typesOrder) {
            $positionMeasure1 = array_search($measure1->getType(), $typesOrder);
            $positionMeasure2 = array_search($measure2->getType(), $typesOrder);

            return ($positionMeasure1 < $positionMeasure2) ? -1 : 1;
        });

        return new ArrayCollection($measures);
    }

    public function addMeasure(Measure $measure): void
    {
        if (!$this->measures->contains($measure)) {
            $measure->setCity($this);
            $this->measures->add($measure);
        }
    }

    public function removeMeasure(Measure $measure): void
    {
        $this->measures->removeElement($measure);
    }

    public function getMarkers(): Collection
    {
        return new ArrayCollection(array_values($this->markers->toArray()));
    }

    public function addMarker(Marker $marker): void
    {
        if (!$this->markers->contains($marker)) {
            $marker->setCity($this);
            $this->markers->add($marker);
        }
    }

    public function removeMarker(Marker $marker): void
    {
        $this->markers->removeElement($marker);
    }

    public function exportPostalCodes(): string
    {
        return implode(', ', $this->postalCodes ?? []);
    }

    public function getCoordinates(): array
    {
        return [
            'lat' => (float) $this->latitude,
            'lng' => (float) $this->longitude,
        ];
    }

    public static function normalizeCode(string $inseeCode): string
    {
        return str_pad($inseeCode, 5, '0', \STR_PAD_LEFT);
    }

    public function getIndexOptions(): array
    {
        return [
            'searchableAttributes' => [
                'name',
                'postalCodes',
                'inseeCode',
            ],
        ];
    }
}
