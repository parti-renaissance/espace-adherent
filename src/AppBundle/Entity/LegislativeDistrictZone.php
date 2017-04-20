<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="legislative_district_zones", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="legislative_district_zones_area_code_unique", columns="area_code")
 * })
 */
class LegislativeDistrictZone
{
    private const TYPE_DEPARTMENT = 'departement';
    private const TYPE_REGION = 'region';

    /**
     * @ORM\Column(type="smallint", options={"unsigned": true})
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(length=4)
     */
    private $areaCode;

    /**
     * @ORM\Column(length=20)
     */
    private $areaType;

    /**
     * @ORM\Column(length=100)
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     */
    private $keywords;

    public static function createDepartmentZone(string $areaCode, string $name, array $keywords = []): self
    {
        return new self($areaCode, self::TYPE_DEPARTMENT, $name, $keywords);
    }

    public static function createRegionZone(string $areaCode, string $name, array $keywords = []): self
    {
        return new self($areaCode, self::TYPE_REGION, $name, $keywords);
    }

    public function __construct(string $areaCode, string $type, string $name, array $keywords = [])
    {
        $this->areaCode = $areaCode;
        $this->setAreaType($type);
        $this->setKeywords($keywords);
        $this->setName($name);
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): int
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

    public function getAreaType(): string
    {
        return $this->areaType;
    }

    public function getAreaCode(): string
    {
        return $this->areaCode;
    }

    public function setAreaCode(string $code): void
    {
        $this->areaCode = $code;
    }

    public function getName(): string
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
