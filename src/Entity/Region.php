<?php

namespace App\Entity;

use App\Address\AddressInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[UniqueEntity(fields: ['code'])]
class Region
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
    #[Assert\Length(max: 10)]
    #[Assert\NotBlank]
    #[ORM\Column(length: 10, unique: true)]
    private $code;

    /**
     * @var string
     */
    #[Assert\Country(message: 'city.country.invalid')]
    #[Assert\NotBlank(message: 'city.country.not_blank')]
    #[ORM\Column(length: 2)]
    private $country;

    /**
     * @var Department[]|Collection
     */
    #[ORM\OneToMany(mappedBy: 'region', targetEntity: Department::class, cascade: ['all'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    private $departments;

    public function __construct(?string $name = null, ?string $code = null, string $country = AddressInterface::FRANCE)
    {
        $this->name = $name;
        $this->code = $code;
        $this->country = $country;
        $this->departments = new ArrayCollection();
    }

    public function __toString()
    {
        return (string) $this->name;
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    public function getDepartments(): Collection
    {
        return $this->departments;
    }

    public function addDepartment(Department $department): void
    {
        if (!$this->departments->contains($department)) {
            $department->setRegion($this);
            $this->departments->add($department);
        }
    }

    public function removeDepartment(Department $department): void
    {
        $this->departments->removeElement($department);
    }
}
