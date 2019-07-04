<?php

namespace AppBundle\Entity\ChezVous;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\ChezVous\MeasureChoiceLoader;
use AppBundle\Entity\AlgoliaIndexedEntityInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ChezVous\CityRepository")
 * @ORM\Table(name="chez_vous_cities")
 *
 * @UniqueEntity("slug")
 * @UniqueEntity("inseeCode")
 *
 * @Algolia\Index(
 *     autoIndex=false,
 *     searchableAttributes={
 *         "name",
 *         "postalCodes"
 *     }
 * )
 */
class City implements AlgoliaIndexedEntityInterface
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(length=100)
     *
     * @Assert\NotBlank
     * @Assert\Length(max="100")
     *
     * @Algolia\Attribute
     */
    private $name;

    /**
     * @var string[]|null
     *
     * @ORM\Column(type="json_array")
     *
     * @Assert\NotBlank
     *
     * @Algolia\Attribute
     */
    private $postalCodes = [];

    /**
     * @var string|null
     *
     * @ORM\Column(length=10, unique=true)
     *
     * @Assert\NotBlank
     * @Assert\Length(max="10")
     *
     * @Algolia\Attribute
     */
    private $inseeCode;

    /**
     * @var float|null
     *
     * @ORM\Column(type="geo_point")
     *
     * @Assert\NotBlank
     */
    private $latitude;

    /**
     * @var float|null
     *
     * @ORM\Column(type="geo_point")
     *
     * @Assert\NotBlank
     */
    private $longitude;

    /**
     * @var string|null
     *
     * @ORM\Column(length=100, unique=true)
     *
     * @Assert\NotBlank
     * @Assert\Length(max="100")
     *
     * @Algolia\Attribute
     */
    private $slug;

    /**
     * @var Department|null
     *
     * @ORM\ManyToOne(targetEntity=Department::class, inversedBy="cities", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Algolia\Attribute
     */
    private $department;

    /**
     * @var Measure[]|Collection
     *
     * @ORM\OneToMany(targetEntity=Measure::class, mappedBy="city", cascade={"all"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     *
     * @Assert\Valid
     */
    private $measures;

    /**
     * @var Marker[]|Collection
     *
     * @ORM\OneToMany(targetEntity=Marker::class, mappedBy="city", cascade={"all"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     *
     * @Assert\Valid
     *
     * @Algolia\Attribute
     */
    private $markers;

    public function __construct(
        Department $department = null,
        string $name = null,
        array $postalCodes = null,
        string $inseeCode = null,
        string $slug = null,
        float $latitude = null,
        float $longitude = null
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

    public function getDepartmentNumber(): ?string
    {
        return $this->departmentNumber;
    }

    public function setDepartmentNumber(?string $departmentNumber): void
    {
        $this->departmentNumber = $departmentNumber;
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

    /**
     * @Algolia\Attribute
     */
    public function getMeasures(): array
    {
        $typesOrder = array_keys(MeasureChoiceLoader::getTypeKeysMap());

        $measures = array_values($this->measures->toArray());

        usort($measures, function (Measure $measure1, Measure $measure2) use ($typesOrder) {
            $positionMeasure1 = array_search($measure1->getType(), $typesOrder);
            $positionMeasure2 = array_search($measure2->getType(), $typesOrder);

            return ($positionMeasure1 < $positionMeasure2) ? -1 : 1;
        });

        return $measures;
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
        return $this->markers;
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

    /**
     * @Algolia\Attribute(algoliaName="coordinates")
     */
    public function getCoordinates(): array
    {
        return [(float) $this->latitude, (float) $this->longitude];
    }

    public static function normalizeCode(string $inseeCode): string
    {
        return str_pad($inseeCode, 5, '0', \STR_PAD_LEFT);
    }
}
