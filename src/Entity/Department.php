<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[UniqueEntity(fields: ['code'])]
class Department
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
    #[Assert\Length(max: 100)]
    #[Assert\NotBlank]
    #[ORM\Column(length: 100)]
    private $name;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 100)]
    #[Assert\NotBlank]
    #[ORM\Column(length: 100, nullable: true)]
    private $label;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 10)]
    #[Assert\NotBlank]
    #[ORM\Column(length: 10, unique: true)]
    private $code;

    /**
     * @var Region|null
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Region::class, fetch: 'EAGER', inversedBy: 'departments')]
    private $region;

    /**
     * @var City[]|Collection
     */
    #[ORM\OneToMany(mappedBy: 'department', targetEntity: City::class, cascade: ['all'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    private $cities;

    public function __construct(?Region $region = null, ?string $name = null, ?string $label = null, ?string $code = null)
    {
        $this->name = $name;
        $this->label = $label;
        $this->code = $code;
        $this->region = $region;
        $this->cities = new ArrayCollection();
    }

    public function __toString()
    {
        return \sprintf('%s (%s)', $this->name, $this->code);
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

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    public function getRegion(): ?Region
    {
        return $this->region;
    }

    public function setRegion(?Region $region): void
    {
        $this->region = $region;
    }

    public function getCities(): Collection
    {
        return $this->cities;
    }

    public function addCity(City $city): void
    {
        if (!$this->cities->contains($city)) {
            $city->setDepartment($this);
            $this->cities->add($city);
        }
    }

    public function removeCity(City $city): void
    {
        $this->cities->removeElement($city);
    }
}
