<?php

namespace AppBundle\Entity\Nomenclature;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Nomenclature\SenatorAreaRepository")
 * @ORM\Table(name="nomenclature_senator_area", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="senator_area_code_unique", columns="code")
 * })
 * @UniqueEntity(fields="code", message="legislative_district_zone.area_code.unique")
 *
 * @Algolia\Index(autoIndex=false)
 */
class SenatorArea
{
    /**
     * @ORM\Column(type="smallint", options={"unsigned": true})
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(length=6)
     *
     * @Assert\NotBlank
     */
    private $code;

    /**
     * @ORM\Column(length=100)
     *
     * @Assert\NotBlank
     * @Assert\Length(min=2, max=100)
     */
    private $name;

    /**
     * @ORM\Column(type="simple_array")
     * @Assert\Count(min=1)
     */
    private $keywords;

    public function __construct(string $code, string $name, array $keywords = [])
    {
        $this->code = $code;
        $this->name = $name;
        $this->keywords = $keywords;
    }

    public function __toString(): string
    {
        return sprintf('%s - %s', $this->code, $this->name);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getZoneNumber(): string
    {
        return ltrim($this->code, '0');
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

    public function getKeywords(): array
    {
        return $this->keywords;
    }

    public function setKeywords(array $keywords): void
    {
        $this->keywords = $keywords;
    }

    private function addKeyword(string $keyword): void
    {
        if (!\in_array($keyword, $this->keywords, true)) {
            $this->keywords[] = $keyword;
        }
    }
}
