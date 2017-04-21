<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\LegislativeDistrictZoneRepository")
 * @ORM\Table(name="legislative_district_zones", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="legislative_district_zones_area_code_unique", columns="area_code")
 * })
 * @UniqueEntity(fields="areaCode", message="legislative_district_zone.area_code.unique", groups="Admin")
 */
class LegislativeDistrictZone
{
    private const TYPE_DEPARTMENT = 'departement';
    private const TYPE_REGION = 'region';

    const ZONE_FRANCE = 'France métropolitaine';
    const ZONE_DOM_TOM = 'DOM-TOM';
    const ZONE_FOREIGN = 'Étranger';

    const TYPE_CHOICES = [
        'Département (DOM-TOM inclus)' => self::TYPE_DEPARTMENT,
        'Autre région du monde' => self::TYPE_REGION,
    ];

    /**
     * @ORM\Column(type="smallint", options={"unsigned": true})
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(length=4)
     * @Assert\NotBlank(groups="Admin")
     * @Assert\Regex(
     *   pattern="/^[0-1]\d{3}$/",
     *   message="legislative_district_zone.area_code.invalid",
     *   groups="Admin"
     * )
     */
    private $areaCode;

    /**
     * @ORM\Column(length=20)
     * @Assert\NotBlank(groups="Admin")
     * @Assert\Choice(
     *   callback = "getAreaTypeChoices",
     *   message="legislative_district_zone.area_type.invalid",
     *   strict=true,
     *   groups="Admin"
     * )
     */
    private $areaType = self::TYPE_DEPARTMENT;

    /**
     * @ORM\Column(length=100)
     * @Assert\NotBlank(groups="Admin")
     * @Assert\Length(min=2, max=100, groups="Admin")
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     */
    private $keywords;

    public static function createDepartmentZone(string $areaCode, string $name, array $keywords = []): self
    {
        return self::create($areaCode, self::TYPE_DEPARTMENT, $name, $keywords);
    }

    public static function createRegionZone(string $areaCode, string $name, array $keywords = []): self
    {
        return self::create($areaCode, self::TYPE_REGION, $name, $keywords);
    }

    private static function create(string $areaCode, string $areaType, string $name, array $keywords = []): self
    {
        $zone = new self();
        $zone->setAreaCode($areaCode);
        $zone->setAreaType($areaType);
        $zone->setKeywords($keywords);
        $zone->setName($name);

        return $zone;
    }

    public static function getAreaTypeChoices(): array
    {
        return array_values(self::TYPE_CHOICES);
    }

    public function __toString(): string
    {
        $areaCode = (int) $this->areaCode;
        if ($areaCode < 10) {
            $code = substr($this->areaCode, 2);
        } else {
            $code = ltrim($this->areaCode, '0');
        }

        return sprintf('%s - %s', $code, $this->name);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setAreaType(string $type): void
    {
        if (!in_array($type = strtolower($type), $types = [self::TYPE_DEPARTMENT, self::TYPE_REGION])) {
            throw new \InvalidArgumentException(sprintf('Invalid district zone type "%s". It must be one of %s.', $type, implode(', ', $types)));
        }

        $this->areaType = $type;
    }

    public function getAreaType(): ?string
    {
        return $this->areaType;
    }

    public function getAreaCode(): ?string
    {
        return $this->areaCode;
    }

    public function setAreaCode(string $code): void
    {
        $this->areaCode = $code;
    }

    final public function getAreaTypeLabel(): string
    {
        $areaCode = (int) $this->areaCode;

        if ($areaCode <= 95) {
            return self::ZONE_FRANCE;
        }

        if ($areaCode >= 971 && $areaCode <= 989) {
            return self::ZONE_DOM_TOM;
        }

        if ($areaCode >= 1000) {
            return self::ZONE_FOREIGN;
        }

        throw new \RuntimeException(sprintf('Unexpected code "%s" for zone "%s"', $areaCode, $this->name));
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
        $this->addKeyword($name);
    }

    public function addKeyword(string $keyword): void
    {
        $keywords = $this->getKeywords();
        $keywords[] = $keyword;

        if ($keyword !== $lowercase = mb_strtolower($keyword, 'UTF-8')) {
            $keywords[] = $lowercase;
        }

        $this->setKeywords($keywords);
    }

    public function removeKeyword(string $keyword): void
    {
        $keywords = $this->getKeywords();

        if (false !== $key = array_search($keyword, $keywords, true)) {
            unset($keywords[$key]);
        }

        if (false !== $key = array_search(mb_strtolower($keyword, 'UTF-8'), $keywords, true)) {
            unset($keywords[$key]);
        }

        $this->setKeywords($keywords);
    }

    /**
     * @Assert\Count(min=1, groups="Admin")
     */
    public function getKeywords(): array
    {
        if (empty($this->keywords)) {
            return [];
        }

        return explode("\n", $this->keywords);
    }

    public function setKeywords(array $keywords): void
    {
        $this->keywords = implode("\n", array_unique($keywords));
    }
}
