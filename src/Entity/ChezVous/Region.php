<?php

namespace App\Entity\ChezVous;

use App\Repository\ChezVous\RegionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RegionRepository::class)]
#[ORM\Table(name: 'chez_vous_regions')]
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
    #[Assert\Length(max: '100')]
    #[Assert\NotBlank]
    #[ORM\Column(length: 100)]
    private $name;

    /**
     * @var string|null
     */
    #[Assert\Length(max: '10')]
    #[Assert\NotBlank]
    #[ORM\Column(length: 10, unique: true)]
    private $code;

    /**
     * @var Department[]|Collection
     */
    #[ORM\OneToMany(mappedBy: 'region', targetEntity: Department::class, cascade: ['all'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    private $departments;

    public function __construct(?string $name = null, ?string $code = null)
    {
        $this->name = $name;
        $this->code = $code;
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
